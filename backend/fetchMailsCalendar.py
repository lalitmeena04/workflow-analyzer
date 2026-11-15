from api_services import make_api_call
from customfunctions import update_user_tokens_on_database
from project_constants import graph_endpoint, db_config
import mail_extraction
import os
import json
import requests
import mysql.connector


def get_projects():  # Get list of projects from server
    """
        Function to get the project names from database
    :return: None
    """

    cnx = mysql.connector.connect(**db_config)
    print("Connection to DB Successful!")
    cursor = cnx.cursor()

    query = "SELECT proj_name FROM wf_projects WHERE status = 1"
    cursor.execute(query)
    projects = cursor.fetchall()
    print(projects)
    for project in projects:
        print(project[0])
        # Checking if any user has not given consent to access emails
        # if yes, the project will be skipped

        query = 'Select COUNT(email) FROM wf_emails WHERE proj_name = "Workflow Analyzer" && access_token = ""'
        cursor.execute(query)
        resp = cursor.fetchall()
        print(resp[0][0])

        if resp[0][0] == 0:
            # Updating the access tokens before calling the graph API
            update_user_tokens_on_database(project[0])
            print("\n\n\nGET MAILS WILL BE CALLED NOW")
            get_mails_of_project(project[0])
            print("\n\n\n\n\n\n\nGET Calendar WILL BE CALLED NOW")
            get_calendar_events(project[0])
        else:
            print("in Continue")
            continue

    cnx.close()


def get_mails_of_project(project_title):
    print("\nget_mails_of_project() here...")
    file_name = project_title
    file_name = file_name + ".json"
    print("file name in get_mails_of_project = ", file_name)
    if os.path.isfile(file_name):
        os.remove(file_name)
        print("Previous file deleted!!!")
    else:
        print("Previous File don't EXIST!!")

    cnx = mysql.connector.connect(**db_config)
    print("Connection to DB Successful!")
    cursor = cnx.cursor()
    query = "SELECT * FROM wf_emails WHERE proj_name = '{0}' ".format(project_title)
    print("pro_title = ", project_title)
    cursor.execute(query)
    results = cursor.fetchall()

    for user_details in results:

        pro_title = user_details[2]     # This is project title received from database
        acc_token = user_details[3]
        id = user_details[5]            # Account Id of the email address on microsoft graph

        print('\n\n\n\nid = ', id)
        print('pro_title=', pro_title)
        print("Access token = ", acc_token)

        fetch_mail_of_user_by_id(id, acc_token, file_name)

    cnx.close()


def fetch_mail_of_user_by_id(id, token, file_name):
    print("fetch_mail_of_user_by_id() here")
    str_mail = '/users/' + id + "/mailFolders('Inbox')/messages"

    print(str_mail)
    get_messages_url = graph_endpoint.format(str_mail)
    query_parameters = {'$top': '50', '$select': 'receivedDateTime,subject,from,body',
                        '$orderby': 'receivedDateTime DESC'}

    r = make_api_call('GET', get_messages_url, token, parameters=query_parameters)

    if r.status_code == requests.codes.ok:

        mails = []

        print("\n\n\nOK RECEIVED THE MAILS... \n\n Writing to JSON file name {0}".format(file_name))

        print(file_name)
        if not os.path.isfile(file_name):
            mails.append(r.json())
            with open(file_name, mode='w') as f:
                f.write(json.dumps(mails))
            f.close()
        else:
            with open(file_name) as feeds_json:
                feeds = json.load(feeds_json)
            feeds_json.close()

            feeds.append(r.json())

            with open(file_name, mode='w') as f:
                f.write(json.dumps(feeds))
            f.close()
        print("MAILS STORED in {0} file".format(file_name))
        return "THANKS... Data stored"  # r.json()

    else:
        print("error fetching mails!!!")
        return "{0}: {1}".format(r.status_code, r.text)


def get_calendar_events(project_title):
    cnx = mysql.connector.connect(**db_config)
    print("Connection to DB Successful!")
    cursor = cnx.cursor()

    query = "SELECT proj_mgr FROM wf_projects WHERE proj_name = '{0}' ".format(project_title)

    # print("pro_title = ", project_title)
    cursor.execute(query)
    results = cursor.fetchall()
    # print(results[0][0])

    query = "SELECT access_token,id FROM wf_emails WHERE email = '{0}' ".format(results[0][0])

    cursor.execute(query)
    res = cursor.fetchall()

    # print(res)
    access_token = res[0][0]
    usr_id = res[0][1]
    # print(access_token)
    # print(usr_id)
    get_events_url = graph_endpoint.format('/users/{0}/events'.format(usr_id))

    query_parameters = {'$top': '5',
                        '$select': 'subject,start',
                        '$orderby': 'start/dateTime DESC'}

    r = make_api_call('GET', get_events_url, access_token, parameters=query_parameters)

    if r.status_code == requests.codes.ok:
        data = r.json()
        # print(data)

        # print(type(data))
        data = data['value']
        # print(type(data))
        # print(data)
        # print(len(data))
        # print("BLANK LINES ahead...\n\n\n\n")

        for i in data:
            sub = i['subject']              # Subject in the data list
            start = i['start']['dateTime']
            # print(sub)
            # print(start)

            query = 'SELECT * FROM wf_calendar WHERE proj_name = "{0}" && subject = "{1}" && start_date = "{2}"'.format(project_title, sub, start)

            cursor.execute(query)
            resp = cursor.fetchall()
            print(resp)
            print(len(resp))

            if len(resp) == 0:
                query = 'INSERT INTO wf_calendar VALUES ("{0}" ,"{1}" ,"{2}") '.format(project_title, sub, start)
                # print(query)
                cursor.execute(query)
                cnx.commit()

        cnx.close()

    else:
        print("Error fetching Calendar")
        print(r.status_code)
        print(r.text)
        return "{0}: {1}".format(r.status_code, r.text)
