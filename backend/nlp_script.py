#Importing dependencies
from nltk.sentiment.vader import SentimentIntensityAnalyzer
from project_constants import db_config
import os
import glob
import pandas as pd
import datetime
import dateparser
import mysql.connector
import datefinder
import spacy


def start():
    print('nlp_script_starts')
    datefinder.ValueError = ValueError, OverflowError
    nlp = spacy.load('en_core_web_sm')

    #Checking & deleting old file
    if os.path.isfile("{0}.csv".format('combined_csv')):
        os.remove('{0}.csv'.format('combined_csv'))

    all_filenames = [i for i in glob.glob('*.{}'.format('csv'))]
    #combine all files in the list
    combined_csv = pd.concat([pd.read_csv(f) for f in all_filenames ])
    #export to csv
    combined_csv.to_csv( "combined_csv.csv", index=False, encoding='utf-8-sig')

    # Reading data
    # wf_mails table
    mails = pd.read_csv('combined_csv.csv', index_col=0)


    cnx = mysql.connector.connect(**db_config)
    cursor = cnx.cursor()
    query = "SELECT * FROM wf_modules"
    modules = pd.read_sql(query,cnx)
    cnx.commit()
    cursor.close()
    cnx.close()
    modules['start_date'] = modules['start_date'].astype(str) + ' 00:00:00'
    modules['deadline'] = modules['deadline'].astype(str) + ' 00:00:00'
    start_date = modules['start_date'].copy()
    deadline = modules['deadline'].copy()

    ## User Defined functions 

    #Function to count days between two dates
    def count_days(date1,date2):
        datetimeFormat = '%Y-%m-%d %H:%M:%S'
        diff = datetime.datetime.strptime(date2, datetimeFormat)\
        - datetime.datetime.strptime(date1, datetimeFormat)
        return abs(diff.days)


    #Functions to extract dates from string
    def find_date(string):
        date_list = []
        matches = datefinder.find_dates(string)
        for match in matches:
            date_list.append(match)
        if(len(date_list)!=0):
            d = date_list[0]
            return d


    # Parse_date New Version
    def parse_date(string):
        str_list = []
        bodystr = nlp(string)
        for ent in bodystr.ents:
            if(ent.label_=='DATE'):            
                str_list.append(ent.text)
        if(len(str_list)==0):
            for ent in bodystr.ents:
                if(ent.label_=='TIME'):
                    str_list.append(ent.text)
        if(len(str_list)!=0):
            s = dateparser.parse(str_list[0]).strftime("%Y-%m-%d %H:%M:%S")
            return s
        else:
            if(type(dateparser.parse(string))!=type(None)):
                s = dateparser.parse(string).strftime("%Y-%m-%d %H:%M:%S")
                return s


    # Function to extract any format of date from a string
    def date_from_body(string):
        date_list = []
        date_list.append(find_date(string))
        if(type(date_list[0])==type(None)):
            date_list = []
        if(len(date_list)==0):
            date_list.append(parse_date(string))
        if(type(date_list[0])==type(None)):
            date_list = []
        if(len(date_list)!=0):
            return date_list[0]


    # Function to calculate progress of a particular module
    def progress(start_date,deadline):
        current_datetime = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        if(current_datetime >= start_date):
            total_days = count_days(start_date,deadline)
            if(current_datetime <= deadline):
                current_days = count_days(start_date,current_datetime)
            else:
                current_days = count_days(start_date,deadline)
            try:
                x = total_days/current_days
            except ZeroDivisionError:
                x = 0
            try:
                prog = 100/x
            except ZeroDivisionError:
                prog = 0
            return prog
        else:
            return 0


    # remove punctuations from string
    def remove_punctuations(string):
        punctuations = '''!()-[]{};:'"\,<>./?@#$%^&*_~'''
        no_punct = ""
        for char in string:
            if char not in punctuations:
                no_punct = no_punct + char
        return no_punct


    #Writing progress into database
    def write_progress(exp_prog,curr_prog,mod_name,proj_name):
        cnx = mysql.connector.connect(**db_config)
        cursor = cnx.cursor()
        query_string = "INSERT INTO wf_module_progress (module,proj_name,exp_progress,curr_progress) VALUES ('{0}','{1}','{2}','{3}')".format(mod_name,proj_name,exp_prog,curr_prog)
        cursor.execute(query_string)
        cnx.commit()
        cursor.close()
        cnx.close()


    def write_previous_progress(mod_name,proj_name):
        cnx = mysql.connector.connect(**db_config)
        cursor = cnx.cursor()
        query_string = "INSERT INTO wf_module_progress (module,proj_name,exp_progress,curr_progress) (SELECT module,proj_name,exp_progress,curr_progress FROM wf_module_progress WHERE log=(SELECT max(log) FROM wf_module_progress WHERE module='{0}' AND proj_name='{1}'))".format(mod_name,proj_name)
        cursor.execute(query_string)
        cnx.commit()
        cursor.close()
        cnx.close()


    # Sentiment Analysis on e-mails
    SIA = SentimentIntensityAnalyzer()
    mails['polarity_score'] = mails.body.apply(lambda x: SIA.polarity_scores(x)['compound'])
    mails['neutral_score'] = mails.body.apply(lambda x: SIA.polarity_scores(x)['neu'])
    mails['negative_score'] = mails.body.apply(lambda x: SIA.polarity_scores(x)['neg'])
    mails['positive_score'] = mails.body.apply(lambda x: SIA.polarity_scores(x)['pos'])
    mails['sentiment'] = ''
    mails.loc[mails.polarity_score > 0, 'sentiment'] = 'POSITIVE'
    mails.loc[mails.polarity_score == 0, 'sentiment'] = 'NEUTRAL'
    mails.loc[mails.polarity_score < 0, 'sentiment'] = 'NEGATIVE'
    Negative, pos, neu = mails.sentiment.value_counts()
    Positive = pos + neu
    if Negative > Positive:
        overalloutput = -1
    elif Negative == Positive:
        overalloutput = 1
    else:
        overalloutput = 1
        
    # Final Code
    # Mapping the mails related to particular project's module
    # Calculating & writing current progress in database
    flag = 0

    for i in range(len(modules)):
        proj_mod_name = modules['proj_name'][i] +' '+ modules['module'][i]
        for j in range(len(mails)):
            sub_string = mails['subject'][j]
            proj_mod_str = remove_punctuations(proj_mod_name).split()
            sub_str = remove_punctuations(sub_string).split()
            count = 0
            for k in range(len(proj_mod_str)):
                if proj_mod_str[k] in sub_str:
                    count = count+1

            if(len(proj_mod_str)==count):
                #print('Match',i,j)
                if((mails['sentiment'][j])=='NEGATIVE'):
                    print('1')
                    delayed_date = []
                    delayed_date.append(date_from_body(mails['body'][j]))
                    if(type(delayed_date[0])!=type(None)):
                        curr_prog = progress(start_date[i],delayed_date[0])
                        exp_prog = progress(start_date[i],deadline[i])
                        write_progress(exp_prog,curr_prog,modules['module'][i],modules['proj_name'][i])
                        flag = 1
                    else:
                        write_previous_progress(modules['module'][i],modules['proj_name'][i])
                        flag = 1
                elif((mails['sentiment'][j])=='POSITIVE'):
                    increment_date = []
                    increment_date.append(date_from_body(mails['body'][j]))
                    if(type(increment_date[0])!=type(None)):
                        curr_prog = progress(start_date[i],increment_date[0])
                        exp_prog = progress(start_date[i],deadline[i])
                        write_progress(exp_prog,curr_prog,modules['module'][i],modules['proj_name'][i])
                        flag = 1
                    else:
                        exp_prog = progress(start_date[i],deadline[i])
                        write_progress(exp_prog,exp_prog,modules['module'][i],modules['proj_name'][i])
                        flag = 1

                else:
                    prog = progress(start_date[i],deadline[i])
                    write_progress(prog,prog,modules['module'][i],modules['proj_name'][i])
                    flag = 1
    if(flag==0):
        for i in range(len(modules)):
            write_previous_progress(modules['module'][i],modules['proj_name'][i])


