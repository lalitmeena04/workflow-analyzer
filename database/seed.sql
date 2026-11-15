-- Optional demo data so the dashboard charts have something to show.
-- Load after schema.sql:
--   mysql -u <user> -p <database> < seed.sql
--
-- The demo manager logs in as demo@example.com with password "password".
-- The progress figures are the real (non-personal) numbers recorded while
-- the project tracked its own development in April 2019.

-- bcrypt hash of the word "password"
INSERT INTO `wf_user` (`username`, `password`, `name`) VALUES
('demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo Manager');

INSERT INTO `wf_projects` (`proj_name`, `proj_mgr`, `start_date`, `end_date`, `deadline`, `status`, `uuid`) VALUES
('Workflow Analyzer', 'demo@example.com', '2019-03-31 18:30:00', '0000-00-00 00:00:00', '2019-04-09 10:30:00', 1, '');

INSERT INTO `wf_emails` (`email`, `name`, `proj_name`, `access_token`, `refresh_token`, `id`) VALUES
('demo@example.com', 'Demo Manager', 'Workflow Analyzer', '', '', '');

INSERT INTO `wf_modules` (`log`, `proj_name`, `module`, `start_date`, `deadline`, `end_date`) VALUES
('2019-04-07 05:16:00', 'Workflow Analyzer', 'UI',         '2019-03-31 18:30:00', '2019-04-08 18:30:00', '0000-00-00 00:00:00'),
('2019-04-07 05:16:01', 'Workflow Analyzer', 'OutlookAPI', '2019-03-31 18:30:00', '2019-04-04 18:30:00', '0000-00-00 00:00:00'),
('2019-04-07 05:16:01', 'Workflow Analyzer', 'NLP',        '2019-03-31 18:30:00', '2019-04-08 18:30:00', '0000-00-00 00:00:00');

INSERT INTO `wf_module_progress` (`log`, `module`, `proj_name`, `exp_progress`, `curr_progress`) VALUES
('2019-03-31 18:30:00', 'UI',         'Workflow Analyzer',   0,   0),
('2019-03-31 18:30:00', 'OutlookAPI', 'Workflow Analyzer',   0,   0),
('2019-03-31 18:30:00', 'NLP',        'Workflow Analyzer',   0,   0),
('2019-04-01 18:30:00', 'UI',         'Workflow Analyzer',  13,  11),
('2019-04-01 18:30:00', 'OutlookAPI', 'Workflow Analyzer',  25,  25),
('2019-04-01 18:30:00', 'NLP',        'Workflow Analyzer',  13,  10),
('2019-04-02 18:30:00', 'UI',         'Workflow Analyzer',  25,  25),
('2019-04-02 18:30:00', 'OutlookAPI', 'Workflow Analyzer',  50,  50),
('2019-04-02 18:30:00', 'NLP',        'Workflow Analyzer',  25,  20),
('2019-04-03 18:30:00', 'UI',         'Workflow Analyzer',  38,  33),
('2019-04-03 18:30:00', 'OutlookAPI', 'Workflow Analyzer',  75,  75),
('2019-04-03 18:30:00', 'NLP',        'Workflow Analyzer',  38,  30),
('2019-04-04 18:30:00', 'UI',         'Workflow Analyzer',  50,  50),
('2019-04-04 18:30:00', 'OutlookAPI', 'Workflow Analyzer', 100, 100),
('2019-04-04 18:30:00', 'NLP',        'Workflow Analyzer',  50,  40),
('2019-04-05 18:30:00', 'UI',         'Workflow Analyzer',  63,  63),
('2019-04-05 18:30:00', 'OutlookAPI', 'Workflow Analyzer', 100, 100),
('2019-04-05 18:30:00', 'NLP',        'Workflow Analyzer',  63,  63),
('2019-04-06 18:30:00', 'UI',         'Workflow Analyzer',  75,  75),
('2019-04-06 18:30:00', 'OutlookAPI', 'Workflow Analyzer', 100, 100),
('2019-04-06 18:30:00', 'NLP',        'Workflow Analyzer',  75,  75),
('2019-04-07 18:30:00', 'UI',         'Workflow Analyzer',  88,  88),
('2019-04-07 18:30:00', 'OutlookAPI', 'Workflow Analyzer', 100, 100),
('2019-04-07 18:30:00', 'NLP',        'Workflow Analyzer',  88,  70),
('2019-04-08 18:30:00', 'UI',         'Workflow Analyzer', 100,  89),
('2019-04-08 18:30:00', 'OutlookAPI', 'Workflow Analyzer', 100, 100),
('2019-04-08 18:30:00', 'NLP',        'Workflow Analyzer', 100,  80),
('2019-04-09 18:30:00', 'UI',         'Workflow Analyzer', 100, 100),
('2019-04-09 18:30:00', 'OutlookAPI', 'Workflow Analyzer', 100, 100),
('2019-04-09 18:30:00', 'NLP',        'Workflow Analyzer', 100, 100);

INSERT INTO `wf_calendar` (`proj_name`, `subject`, `start_date`) VALUES
('Workflow Analyzer', 'Project discussion meeting', '2019-04-12 07:22:29'),
('Workflow Analyzer', 'Weekly discussion meeting',  '2019-04-13 07:23:17');

INSERT INTO `wf_sentiments` (`score`, `proj_name`, `log`) VALUES
(1, 'Workflow Analyzer', '2019-04-10 06:27:46'),
(0, 'Workflow Analyzer', '2019-04-10 07:31:49'),
(1, 'Workflow Analyzer', '2019-04-10 08:34:47'),
(1, 'Workflow Analyzer', '2019-04-10 08:35:28');
