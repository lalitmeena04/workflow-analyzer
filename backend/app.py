from flask import Flask, render_template, redirect, url_for, request
from api_services import generate_sign_in_url, get_token_from_code, get_user_profile_details
from customfunctions import save_user_details_to_database, check_manager, send_mail_to_members
from project_constants import dashboard_url


def _force_https(wsgi_app):
    def wrapper(environ, start_response):
        environ['wsgi.url_scheme'] = 'https'
        return wsgi_app(environ, start_response)
    return wrapper


app = Flask(__name__)
app.wsgi_app = _force_https(app.wsgi_app)


@app.route('/')
def index():
    return render_template('404.html'), 404


@app.route('/emp_consent/')
def emp_consent():
    redirect_uri = request.host_url[:-1]+url_for('gettoken', person_type="emp")
    print(redirect_uri)
    sign_in_url = generate_sign_in_url(redirect_uri, False)
    return render_template('emp_consent.html', signin_url=sign_in_url)


@app.route('/man_consent/')
def man_consent():
    redirect_uri = request.host_url[:-1]+url_for('gettoken', person_type="man")
    sign_in_url = generate_sign_in_url(redirect_uri, True)
    return render_template('man_consent.html', url=sign_in_url)


@app.route('/end_emp/')
def end_emp():
    return render_template('end_emp.html')


@app.route('/end_man/')
def end_man():
    return render_template('end_man.html', dashboard_url=dashboard_url)


@app.errorhandler(404)
def page_not_found(e):
    return render_template('404.html'), 404


@app.route('/sendmail/')
def sendmail():
    # FUnction call                 <string:email>, <string:uuid>   email, uuid
    email = request.args.get('email')
    uuid = request.args.get('uuid')
    print(email, ' \n', uuid)
    send_mail_to_members(email, uuid, to='')
    return "OK"


@app.route('/gettoken/<string:person_type>')
def gettoken(person_type):

    auth_code = request.args.get('code')
    redirect_uri = request.host_url[:-1]+url_for('gettoken', person_type=person_type)
    print(redirect_uri)
    token = get_token_from_code(auth_code, redirect_uri, person_type)

    access_token = token['access_token']
    refresh_token = token['refresh_token']

    user = get_user_profile_details(access_token)

    save_user_details_to_database(user, access_token, refresh_token)

    if check_manager(user):             # Check if the user is a manager or not
        return redirect(url_for('end_man'))
    else:
        return redirect(url_for('end_emp'))


if __name__ == '__main__':
    app.run()
