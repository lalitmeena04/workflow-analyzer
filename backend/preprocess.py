from project_constants import db_config
import json
import re
import os
import mysql.connector


def start():
    cnx = mysql.connector.connect(**db_config)
    print("Connection to DB Successful!")
    cursor = cnx.cursor()

    query = "SELECT proj_name FROM wf_projects WHERE status = 1"
    cursor.execute(query)
    results = cursor.fetchall()

    ''' In variable data file name mail.json is stored in it for future working '''
    for x in results:
        if not os.path.isfile('{0}.json'.format(x[0])):
            print('Error File = "{0}.json" Not Found'.format(x[0]))
        else:
            data = json.load(open('{0}.json'.format(x[0])))
            for i in data:
                mails = i
                for j in range(10):
                    value = mails.get('value')[j]

                    id = value.get('id')
                    sqlquery = "select * from wf_mails where id = '{0}'".format(id)
                    uniqueidcheck = cursor.execute(sqlquery)
                    if not uniqueidcheck:
                        subject = value.get('subject')

                        sqlquery = 'select proj_name from wf_projects WHERE status = 1'

                        cursor.execute(sqlquery)
                        project_name_list = []
                        project_name_tuple = cursor.fetchall()

                        for m in project_name_tuple:
                            for n in m:
                                project_name_list.append(n)

                        for k in project_name_list:
                            if k in subject:
                                body = value.get('body')
                                body = body.get('content')

                                mail_id = id
                                mail_body = body
                                query = "INSERT into wf_mails_tags VALUES ({0},{1})".format(mail_id, mail_body)
                                cursor.execute(query)

                                cnx.commit()

                                def cleanhtml(raw_html):
                                    cleanr = re.compile('<[^>]*?>')
                                    cleantext = re.sub(cleanr, '', raw_html)
                                    cleantext = cleantext.replace("&nbsp;", " ")
                                    cleantext = cleantext.replace("&gt;", " ")
                                    cleantext = cleantext.replace("&quot;", " ")
                                    cleantext = cleantext.replace("\'", "\\'")
                                    return cleantext

                                body = cleanhtml(body)
                                # print(body)
                                receivedfrom = value.get('from')
                                receivedfrom = receivedfrom.get('emailAddress')
                                receivedfrom = receivedfrom.get('address')
                                # print(type(receivedfrom))
                                sqlquery = "select * from wf_emails where email = '{0}'".format(receivedfrom)
                                if sqlquery:
                                    def cleandatetime(raw_DateTime):
                                        raw_DateTime = raw_DateTime.replace("T", " ")
                                        raw_DateTime = raw_DateTime.replace("Z", " ")
                                        return raw_DateTime

                                    receivedDateTime = value.get('receivedDateTime')
                                    receivedDateTime = cleandatetime(receivedDateTime)
                                    sqlquery = "INSERT INTO wf_mails(id, proj_name, body, frm, log, subject) " \
                                               "VALUES ('{0}','{1}','{2}','{3}','{4}','{5}')".format(id, k, body,
                                                                                                     receivedfrom,
                                                                                                     receivedDateTime,
                                                                                                     subject)
                                    cursor.execute(sqlquery)
                                    db.commit()
    db.close()


start()
