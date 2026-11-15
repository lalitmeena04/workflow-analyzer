import os

# Database Configuration parameters
# Read from the environment so that no credentials live in the repository
db_config = {
    'user': os.environ.get('WFA_DB_USER', 'root'),
    'password': os.environ.get('WFA_DB_PASSWORD', ''),
    'host': os.environ.get('WFA_DB_HOST', 'localhost'),
    'database': os.environ.get('WFA_DB_NAME', 'workflow_analyzer'),
    'raise_on_warnings': False
}

# Client ID and secret of the Microsoft app registration
client_id = os.environ.get('WFA_CLIENT_ID', '')
client_secret = os.environ.get('WFA_CLIENT_SECRET', '')

# Generic API endpoint
graph_endpoint = 'https://graph.microsoft.com/v1.0{0}'

# Constant strings for OAuth2 flow
# The OAuth authority
authority = 'https://login.microsoftonline.com'

# The authorize URL that initiates the OAuth2 client credential flow for user consent
authorize_url = '{0}{1}'.format(authority, '/common/oauth2/v2.0/authorize?{0}')

# The endpoint issuing token
token_url = '{0}{1}'.format(authority, '/common/oauth2/v2.0/token')

# The scopes required by the app for user's consent
manager_scopes = [
    'openid',
    'User.Read',
    'offline_access',
    'Mail.Read',
    'Calendars.Read',
    'Mail.ReadWrite',
    'Mail.Send',
    ]

employee_scopes = [
    'openid',
    'User.Read',
    'offline_access',
    'Mail.Read',
    'Calendars.Read',
    ]

consent_url = os.environ.get('WFA_CONSENT_URL', 'http://localhost:5000').rstrip('/')
dashboard_url = os.environ.get('WFA_DASHBOARD_URL', 'http://localhost:8000').rstrip('/')

employee_consent_link = consent_url + '/emp_consent/'
