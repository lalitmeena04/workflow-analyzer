import pandas as pd
import re

df = pd.read_csv('wf_mails.csv')
# print(df.body)

body = df['body']
for i in range(len(body)):

    email_body = body[i]

    email_body = email_body.replace("\n", "")
    email_body = email_body.replace("\r", "")
    email_body = email_body.replace("'", "\\'")
    email_body = email_body.replace('"', '\\"')

    email_body = re.sub(re.compile('<[^>]*?>'), '', email_body)
    email_body = email_body.replace("&nbsp;", " ")
    email_body = email_body.replace("&gt;", " ")
    email_body = email_body.replace("&lt;", " ")
    email_body = email_body.replace("&43;", " ")
    email_body = email_body.replace("&quot;", " ")
    email_body = email_body.replace("\'", "\\'")
    body[i] = email_body
    print(email_body)

print(df['body'])
df.to_csv('EMAILS.csv')