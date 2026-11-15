import pandas as pd
from nltk.sentiment.vader import SentimentIntensityAnalyzer
from project_constants import db_config
import mysql.connector

def start():

    def mail_analyzer():
        cnx = mysql.connector.connect(**db_config)
        cursor = cnx.cursor()
        data = pd.read_csv('book.csv')
        SIA = SentimentIntensityAnalyzer()
        data['polarity_score'] = data.Review.apply(lambda x: SIA.polarity_scores(x)['compound'])
        data['neutral_score'] = data.Review.apply(lambda x: SIA.polarity_scores(x)['neu'])
        data['negative_score'] = data.Review.apply(lambda x: SIA.polarity_scores(x)['neg'])
        data['positive_score'] = data.Review.apply(lambda x: SIA.polarity_scores(x)['pos'])
        data['sentiment'] = ''
        data.loc[data.polarity_score > 0, 'sentiment'] = 'POSITIVE'
        data.loc[data.polarity_score == 0, 'sentiment'] = 'NEUTRAL'
        data.loc[data.polarity_score < 0, 'sentiment'] = 'NEGATIVE'
        # print(data.sentiment.value_counts())

        Negative, pos, neu = data.sentiment.value_counts()

        Positive = pos + neu
        if Negative > Positive:
            overalloutput = -1
        elif Negative == Positive:
            overalloutput = 1
        else:
            overalloutput = 1
        proj_name = "Workflow Analyzer"
        sqlquery = "INSERT INTO wf_sentiments(score, proj_name) VALUES ('{0}','{1}')".format(overalloutput, proj_name)
        cursor.execute(sqlquery)
        cnx.commit()
        cnx.close()


    mail_analyzer()


if __name__ == '__main__':
    start()