from api_services import get_new_access_token, make_api_call
from project_constants import db_config, graph_endpoint, employee_consent_link
import mysql.connector


def check_manager(user):
    cnx = mysql.connector.connect(**db_config)
    cursor = cnx.cursor()
    user_email = user.get("userPrincipalName")

    query_string = 'SELECT * FROM wf_user WHERE username = "{0}"'.format(user_email)
    cursor.execute(query_string)

    resp = cursor.fetchall()

    cnx.close()
    if len(resp) == 0:
        return False
    else:
        return True


def save_user_details_to_database(user, a_token, r_token):

    cnx = mysql.connector.connect(**db_config)
    cursor = cnx.cursor()
    user_email = user.get("userPrincipalName")
    user_id = user.get("id")

    query_string = 'UPDATE wf_emails SET access_token = "{0}", refresh_token = "{1}",id = "{2}" WHERE email= "{3}"'.format(a_token, r_token, user_id, user_email)

    cursor.execute(query_string)
    print("Entry done on database")
    cnx.commit()

    cnx.close()


def update_user_tokens_on_database(project_title, default_user_to_update=None):
    """
        Function to update the stored tokens of the user on database, so that new tokens will be used for calling
        the graph API
    :param project_title:
    :param default_user_to_update:
    :return: None
    """

    print("\n\nReached update_user_tokens_on_database()!!!\n")
    cnx = mysql.connector.connect(**db_config)
    print("Connection to DB Successful!")
    cursor = cnx.cursor()

    if default_user_to_update is not None:
        print("DEFAULT USER IS NOT NONE!!!!!!!!! \n\n\n\n")
        print(default_user_to_update)
        query = 'SELECT refresh_token, id FROM wf_emails WHERE email="{0}"'.format(default_user_to_update)
        print(query)
        cursor.execute(query)
        resp = cursor.fetchall()
        print(resp)

        refresh_token = resp[0][0]
        id = resp[0][1]

        print("\n\n\n\n\n\nvalues received from DB")
        print(refresh_token)
        print(id)

        user = {"userPrincipalName": default_user_to_update, "id": id}
        print(user)

        new_token = get_new_access_token(refresh_token, True)       # Setting default as True to update manager's token

        new_access_token = new_token.get('access_token')
        new_refresh_token = new_token.get('refresh_token')
        print("New tokens received!!")
        print("UPDATING New Tokens on Database!!!")
        save_user_details_to_database(user, new_access_token, new_refresh_token)

    else:

        query = "SELECT * FROM wf_emails WHERE proj_name = '{0}' ".format(project_title)
        print("pro_title = ", project_title)
        cursor.execute(query)
        results = cursor.fetchall()

        for user_details in results:
            email = user_details[0]
            refresh_token = user_details[4]
            id = user_details[5]                    # Account Id of the email address on microsoft graph

            user = {"userPrincipalName": email, "id": id}

            new_token = get_new_access_token(refresh_token, check_manager(user))

            new_access_token = new_token.get('access_token')
            new_refresh_token = new_token.get('refresh_token')
            print("New tokens received!!")
            print("UPDATING Database!!!")
            save_user_details_to_database(user, new_access_token, new_refresh_token)


def send_mail_to_members(email, uuid, to):
    update_user_tokens_on_database(project_title='', default_user_to_update=email)

    cnx = mysql.connector.connect(**db_config)
    print("Connection to DB Successful!")
    cursor = cnx.cursor()
    query = 'SELECT COUNT(*) FROM wf_projects WHERE proj_mgr = "{0}" && uuid = "{1}"'.format(email, uuid)
    print(query)

    cursor.execute(query)
    results = cursor.fetchall()
    print(results[0][0])
    if results[0][0] == 1:
        # Removing the UUID from DB
        query = 'UPDATE wf_projects SET uuid = "" WHERE proj_mgr = "{0}" && uuid = "{1}"'.format(email, uuid)
        cursor.execute(query)
        cnx.commit()              # removing the UUID

        # Getting details from DB
        query = 'SELECT proj_name, access_token, id FROM wf_emails WHERE email = "{0}" '.format(email)
        cursor.execute(query)
        results = cursor.fetchall()
        proj_name = results[0][0]
        acc_token = results[0][1]
        usr_id = results[0][2]

        # Getting List of team members of project
        query = 'SELECT email FROM wf_emails WHERE proj_name = "{0}" '.format(proj_name)
        cursor.execute(query)
        results = cursor.fetchall()

        members = []
        for i in results:
            # print(i)
            members.append(i[0])
        to_recipients = []

        for i in members:
            if i == email:
                continue
            to_recipients.append({
                        "emailAddress": {
                            "address": i
                        }
                     })
        print(to_recipients)
        print(type(to_recipients))
        # print(members)

        send_mail_url = graph_endpoint.format('/users/{0}/sendMail'.format(usr_id))

        email_data = {
            "message": {
                "subject": 'Enrollment in new project "{0}"'.format(proj_name),
                "body": {
                    "contentType": "HTML",
                    "content": "<p>Hello, <br> You have been successfully enrolled into {0}.<br>You are needed to give \
                                consent to access your email & calendar data of your outlook account. <br><br> \
                                Kindly, give consent by clicking <a href ='{1}' >HERE</a>.. </p>".format(proj_name, employee_consent_link )
                },
                "toRecipients": to_recipients,
            },
            "saveToSentItems": "true"
        }

        r = make_api_call('POST', send_mail_url, acc_token, email_data)

        if r.status_code == 202:
            print("MAIL Sent!! to: ", to_recipients)
        else:
            print('Mail not sent!!!')
            print(r.status_code)
            print(r.text)

    cnx.close()


# send_mail_to_members('manager@example.com', uuid='12345', to='')
