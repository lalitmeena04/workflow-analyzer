import fetchMailsCalendar
import mail_extraction
import nlp_script
import mailsentimentanalysis

def start_backend():
    fetchMailsCalendar.get_projects()
    mail_extraction.start()
    nlp_script.start()
	mailsentimentanalysis.start()


