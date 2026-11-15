from project_constants import db_config
import mysql.connector
import json
import re
import os
import csv


def start():

    # Connecting to Database
    cnx = mysql.connector.connect(**db_config)
    cursor = cnx.cursor()

    # Getting list of active projects
    sql_query = 'SELECT proj_name FROM wf_projects WHERE status = 1'
    cursor.execute(sql_query)
    project_names = cursor.fetchall()

    for file_name in project_names:
        print(file_name[0])
        if not os.path.isfile('{0}.json'.format(file_name[0])):
            continue

        # Checking if csv already exist
        if os.path.isfile("{0}.csv".format(file_name[0])):
            # Delete the previous CSV
            os.remove('{0}.csv'.format(file_name[0]))

        # importing the emails json file
        mails = json.load(open('{0}.json'.format(file_name[0])))

        for account in mails:
            emails_of_account = account
            # print(emails_of_account)

            for email in emails_of_account.get('value'):
                # print(email)

                email_unique_id = email.get('id')
                # print(email_unique_id)

                sql_query = 'SELECT * FROM wf_mails WHERE id = "{0}"'.format(
                    email_unique_id)  # Query to check if mail is preprocessed
                cursor.execute(sql_query)
                unique_id = cursor.fetchall()
                # print(len(unique_id))

                if len(unique_id) == 0:  # Mail is not preprocessed before...
                    email_subject = email.get('subject')
                    # print(email_subject)
                    # print()
                    if file_name[0] in email_subject:
                        print(file_name[0])
                        email_body = email.get('body').get('content')
                        # email_body = email_body.get('content')
                        # print(email_body)
                        # print("\n\n\n\n")

                        # Replacing \n & \r to <br> in emails for showing on DASHBOARD
                        email_body = email_body.replace("\n", "")
                        email_body = email_body.replace("\r", "")
                        email_body = email_body.replace("'", "\\'")
                        email_body = email_body.replace('"', '\\"')

                        # print(email_body)

                        email_received_from = email.get('from').get('emailAddress').get('address')
                        # print(email_received_from)

                        email_received_date_time = email.get('receivedDateTime')
                        # Cleaning email_received_date_time
                        email_received_date_time = email_received_date_time.replace("T", " ")
                        email_received_date_time = email_received_date_time.replace("Z", " ")
                        # print(email_received_date_time)

                        # Uploading the email to Database
                        query = "INSERT INTO wf_mails(id, proj_name, body, frm, log, subject, `to`) " \
                                "VALUES ('{0}','{1}','{2}','{3}','{4}','{5}', 'codereapers@outlook.com')".format(email_unique_id, file_name[0], email_body,
                                                                                      email_received_from,
                                                                                      email_received_date_time,
                                                                                      email_subject)
                        # print(query)
                        cursor.execute(query)
                        cnx.commit()

                        # Cleaning email body for NLP
                        email_body = re.sub(re.compile('<[^>]*?>'), '', email_body)
                        email_body = email_body.replace("&nbsp;", " ")
                        email_body = email_body.replace("&gt;", " ")
                        email_body = email_body.replace("&lt;", " ")
                        email_body = email_body.replace("&43;", " ")
                        email_body = email_body.replace("&quot;", " ")
                        email_body = email_body.replace("\'", "\\'")
                        # print(email_body)

                        mail = [email_unique_id, email_received_from, email_subject, email_body, file_name[0], email_received_date_time]
                        # print(mail)
                        # Writing Data to CSV File
                        with open('{0}.csv'.format(file_name[0]), 'a', newline='') as myfile:
                            wr = csv.writer(myfile, quoting=csv.QUOTE_ALL)
                            wr.writerow(mail)

            print('\n\n\n')

start()