from urllib.parse import urlencode
from project_constants import authorize_url, client_id, client_secret, manager_scopes, employee_scopes, token_url, graph_endpoint
import requests
import uuid
import json


def make_api_call(method, url, token, payload=None, parameters=None):
    # Send these headers with all API calls
    headers = {
        'User-Agent': 'workflow_analyzer/1.0',
        'Authorization': 'Bearer {0}'.format(token),
        'Accept': 'application/json'
    }

    request_id = str(uuid.uuid4())
    instrumentation = {
        'client-request-id': request_id,
        'return-client-request-id': 'true'
    }

    headers.update(instrumentation)

    response = None

    if method.upper() == 'GET':
        response = requests.get(url, headers=headers, params=parameters)
    elif method.upper() == 'POST':
        headers.update({'Content-Type': 'application/json'})
        response = requests.post(url, headers=headers, data=json.dumps(payload), params=parameters)

    return response


def generate_sign_in_url(redirect_uri, is_manager):
    # Build the query parameters for the sign-in url
    if is_manager is True:
        params = {'client_id': client_id,
                  'redirect_uri': redirect_uri,
                  'response_type': 'code',
                  'scope': ' '.join(str(i) for i in manager_scopes)
                  }
    else:
        params = {'client_id': client_id,
                  'redirect_uri': redirect_uri,
                  'response_type': 'code',
                  'scope': ' '.join(str(i) for i in employee_scopes)
                  }
    sign_in_url = authorize_url.format(urlencode(params))

    return sign_in_url


def get_token_from_code(auth_code, redirect_uri, person_type):
    print("Reached get_token_from_code")
    print(redirect_uri)
    print(auth_code)
    # Build the post form for the token request
    if person_type == "man":
        post_data = { 'grant_type': 'authorization_code',
                      'code': auth_code,
                      'redirect_uri': redirect_uri,
                      'scope': ' '.join(str(i) for i in manager_scopes),
                      'client_id': client_id,
                      'client_secret': client_secret
                      }
    else:
        post_data = { 'grant_type': 'authorization_code',
                      'code': auth_code,
                      'redirect_uri': redirect_uri,
                      'scope': ' '.join(str(i) for i in employee_scopes),
                      'client_id': client_id,
                      'client_secret': client_secret
                      }

    r = requests.post(token_url, data=post_data)

    try:
        print(r.json())
        return r.json()
    except:
        return 'Error retrieving token: {0} - {1}'.format(r.status_code, r.text)


def get_token_from_refresh_token(refresh_token, redirect_uri, is_manager):

    print("\n\nReached get_token_from_refresh_token()\n")

    # Build the post form for the token request
    if is_manager is True:
        post_data = {'grant_type': 'refresh_token',
                     'refresh_token': refresh_token,
                     'redirect_uri': redirect_uri,
                     'scope': ' '.join(str(i) for i in manager_scopes),
                     'client_id': client_id,
                     'client_secret': client_secret
                     }
    else:
        post_data = {'grant_type': 'refresh_token',
                     'refresh_token': refresh_token,
                     'redirect_uri': redirect_uri,
                     'scope': ' '.join(str(i) for i in employee_scopes),
                     'client_id': client_id,
                     'client_secret': client_secret
                     }

    r = requests.post(token_url, data=post_data)

    try:
        return r.json()
    except:
        return 'Error retrieving token: {0} - {1}'.format(r.status_code, r.text)


def get_new_access_token(refresh_token, is_manager):
    print("\n\nReached get_new_access_token()\n\n\n\n Getting new access token")
    if is_manager is True:
        redirect_uri = "http://127.0.0.1:5000/gettoken/man"
    else:
        redirect_uri = "http://127.0.0.1:5000/gettoken/emp"

    print("\nredirect_uri = ", redirect_uri)

    new_tokens = get_token_from_refresh_token(refresh_token, redirect_uri, is_manager)

    # Saving new details
    new_details = {'access_token': new_tokens.get('access_token'), 'refresh_token': new_tokens.get('refresh_token')}
    print(new_details)
    print("\n\n\n\n")
    return new_details


def get_user_profile_details(access_token):
    get_me_url = graph_endpoint.format('/me')

    query_parameters = {'$select': 'givenName,mail,id'}               # Getting the mail & id of the user

    r = make_api_call('GET', get_me_url, access_token, "", parameters=query_parameters)

    if r.status_code == requests.codes.ok:
        print(r.json())
        return r.json()
    else:
        return "{0}: {1}".format(r.status_code, r.text)
