-- This script updates the databases structure before migrating the data from
-- version 2.0 to version 2.1
-- it is intended as a standalone script, however, because of the multiple
-- databases related difficulties, it should be parsed by a PHP script in
-- order to connect to and update the right databases.
-- There is one line per query, allowing the PHP function file() to read
-- all lines separately into an array. The xxMAINxx-type markers are there
-- to tell the PHP script which database we're talking about.
-- By always using the keyword "TABLE" in the queries, we should be able
-- to retrieve and modify the table name from the PHP script if needed, which
-- will allow us to deal with the unique-database-type installations
--
-- This first part is for the main database
-- xxMAINxx
CREATE TABLE IF NOT EXISTS access_url_rel_admin (access_url_id int unsigned NOT NULL,user_id int unsigned NOT NULL,PRIMARY KEY (access_url_id, user_id));
UPDATE settings_current SET access_url_changeable=1 WHERE variable='course_create_active_tools' AND access_url =  1;
UPDATE settings_current SET access_url_changeable=1 WHERE access_url =  1;
CREATE TABLE IF NOT EXISTS search_engine_keywords (id int(11) NOT NULL AUTO_INCREMENT,idobj int(11) DEFAULT NULL,course_code varchar(45) DEFAULT NULL,tool_id varchar(100) DEFAULT NULL,value text,PRIMARY KEY (id));
INSERT INTO settings_current (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable) VALUES('show_catalogue', '', 'radio', 'Platform', 'true', 'ShowCatalogueTitle','ShowCatalogueComment','0',NULL, 1),('automatic_group_filling', '', 'radio', 'Platform', 'true', 'ShowAutomaticGroupTitle','ShowAutomaticGroupComment','0',NULL, 1),('create_new_group', '', 'radio', 'Platform', 'true', 'ShowNewGroupTitle','ShowNewGroupComment','0',NULL, 1),('new_group_seats', '', 'textfield', 'Platform', '20', 'ShowNewGroupSeatTitle','ShowNewGroupSeatComment','0',NULL, 1);
INSERT INTO settings_options(variable, value, display_text) VALUES('show_catalogue','true','Yes'),('show_catalogue','false','No'),('automatic_group_filling','true','Yes'),('automatic_group_filling','false','No'),('create_new_group','true','Yes'),('create_new_group','false','No');
ALTER TABLE session add column description text after name;
ALTER TABLE session add column seats int(11) NOT NULL DEFAULT '-1'  after session_category_id;
ALTER TABLE session add column max_seats int(11) NOT NULL DEFAULT '-1'  after seats;
ALTER TABLE session add column optional_subject int(11) NOT NULL DEFAULT '0'  after max_seats;
ALTER TABLE session_rel_course add column hours int(11) NOT NULL DEFAULT '0' after course_code;
ALTER TABLE session_rel_course add column schedule date after hours;
ALTER TABLE session_rel_course add column time_from varchar(50) NOT NULL DEFAULT '00:00' after schedule;
ALTER TABLE session_rel_course add column time_to varchar(50) NOT NULL DEFAULT '00:00' after time_from;
ALTER TABLE session_rel_course add column repeats varchar(50) after time_to;
ALTER TABLE session_rel_course add column repeats_on varchar(50) after repeats;
ALTER TABLE session_rel_course add column ends_on int(11) NOT NULL DEFAULT '0' after repeats_on;
ALTER TABLE session_rel_course add column occurence varchar(50) after ends_on;
ALTER TABLE session_rel_course add column position int(11) NOT NULL DEFAULT '0' after nbr_users;
ALTER TABLE session_category add column description text after name;
ALTER TABLE session_category add column topic int(11) NOT NULL DEFAULT '0' after description;
ALTER TABLE session_category add column location varchar(250)  after topic;
ALTER TABLE session_category add column modality varchar(250)  after location;
ALTER TABLE session_category add column keywords text after modality;
ALTER TABLE session_category add column student_access varchar(50) after date_end;
ALTER TABLE session_category add column language varchar(50) NOT NULL DEFAULT 'English' after student_access;
ALTER TABLE session_category add column visible char(1) NOT NULL DEFAULT '0' after language;
ALTER TABLE session_category add column cost float NOT NULL DEFAULT '0' after visible;
ALTER TABLE session_category add column currency varchar(250) after cost;
ALTER TABLE session_category add column tax int(11) NOT NULL DEFAULT '0'  after currency;
ALTER TABLE session_category add column method_payment varchar(100)  after tax;
ALTER TABLE session_category add column code varchar(100)  after method_payment;
ALTER TABLE session_category add column inscription_date_start date  after code;
ALTER TABLE session_category add column inscription_date_end date after inscription_date_start;
CREATE TABLE IF NOT EXISTS session_category_rel_user (category_id int(11) NOT NULL DEFAULT '0',user_id int(11) NOT NULL DEFAULT '0',session_id int(11) NOT NULL DEFAULT '0',course_code varchar(200) DEFAULT NULL);
CREATE TABLE IF NOT EXISTS session_rel_category (id smallint(5) unsigned NOT NULL AUTO_INCREMENT,category_id int(11) DEFAULT '0',session_set char(1) NOT NULL DEFAULT '1',session_set_name varchar(255) DEFAULT NULL,session_id int(11) NOT NULL DEFAULT '0',session_range varchar(20) DEFAULT NULL,PRIMARY KEY (id));
CREATE TABLE IF NOT EXISTS topic (id smallint(5) unsigned NOT NULL AUTO_INCREMENT,topic varchar(255) DEFAULT NULL,language char(50) NOT NULL DEFAULT 'English',visible char(1) NOT NULL DEFAULT '0',catalogue_id int(11) DEFAULT NULL,PRIMARY KEY (id));
CREATE TABLE IF NOT EXISTS catalogue (id smallint(5) unsigned NOT NULL AUTO_INCREMENT,title varchar(255) DEFAULT NULL,economic_model char(1) NOT NULL DEFAULT '0',visible char(1) NOT NULL DEFAULT '0',catalogue_display text,payment char(50) DEFAULT NULL,atos_account_number mediumint(8) unsigned NOT NULL DEFAULT '0',paypal_account_ref mediumint(8) unsigned NOT NULL DEFAULT '0',second_installment mediumint(8) unsigned NOT NULL DEFAULT '0',second_installment_delay mediumint(8) unsigned NOT NULL DEFAULT '0',third_installment mediumint(8) unsigned NOT NULL DEFAULT '0',third_installment_delay mediumint(8) unsigned NOT NULL DEFAULT '0',options_selection text,payment_message text,cc_payment_message text,installment_payment_message text,cheque_payment_message text,email char(1) NOT NULL DEFAULT '0',company_logo varchar(255) DEFAULT NULL,company_address text,bank_details text,cheque_message text,terms_conditions text,tva_description text,PRIMARY KEY (id));
CREATE TABLE IF NOT EXISTS slides_management (id int(11) NOT NULL AUTO_INCREMENT,show_slide int(11) NOT NULL DEFAULT '1',slide_speed int(11) NOT NULL DEFAULT '6',PRIMARY KEY (id));
INSERT INTO slides_management(id, show_slide, slide_speed) VALUES (1, 1, 6);
CREATE TABLE IF NOT EXISTS slides (id int(10) unsigned NOT NULL AUTO_INCREMENT,title varchar(255) NOT NULL DEFAULT '',alternate_text varchar(255) NOT NULL DEFAULT '',link varchar(255) NOT NULL DEFAULT '',caption text,image varchar(255) NOT NULL DEFAULT '',language varchar(255) NOT NULL DEFAULT 'English',display_order int(11) NOT NULL,PRIMARY KEY (id));
ALTER TABLE user ADD COLUMN country_code varchar(10) NOT NULL default '';
ALTER TABLE user ADD COLUMN civility     varchar(100) NOT NULL default '';
ALTER TABLE user_field ADD COLUMN field_registration INT DEFAULT 0;
INSERT INTO user_field (field_type, field_variable, field_display_text, field_default_value, field_visible, field_registration, field_changeable, field_filter) VALUES (1, 'organization', 'Organization', '', 0, 1, 1, 1),(1, 'tva_id', 'TVA', '', 0, 1, 1, 1),(1, 'phone', 'Phone', '', 1, 1, 1, 1),(1, 'street', 'Street', '', 1, 1, 1, 1),(1, 'addressline2', 'Address line', '', 1, 1, 1, 1),(1, 'zipcode', 'Zip code', '', 1, 1, 1, 1),(1, 'city', 'City', '', 1, 1, 1, 1);
CREATE TABLE country (id INT  NOT NULL AUTO_INCREMENT, iso VARCHAR(4), original_name VARCHAR(200), langvar VARCHAR(200), iso3 VARCHAR(4)  NOT NULL, numcode VARCHAR(5), PRIMARY KEY (id));
INSERT INTO country(iso, original_name, langvar, iso3, numcode) VALUES('AF','AFGHANISTAN','Afghanistan','AFG','004'),('AL','ALBANIA','Albania','ALB','008'),('DZ','ALGERIA','Algeria','DZA','012'),('AS','AMERICAN SAMOA','AmericanSamoa','ASM','016'),('AD','ANDORRA','Andorra','AND','020'),('AO','ANGOLA','Angola','AGO','024'),('AI','ANGUILLA','Anguilla','AIA','660'),('AG','ANTIGUA AND BARBUDA','AntiguaAndBarbuda','ATG','028'),('AR','ARGENTINA','Argentina','ARG','032'),('AM','ARMENIA','Armenia','ARM','051'),('AW','ARUBA','Aruba','ABW','533'),('AU','AUSTRALIA','Australia','AUS','036'),('AT','AUSTRIA','Austria','AUT','040'),('AZ','AZERBAIJAN','Azerbaijan','AZE','031'),('BS','BAHAMAS','Bahamas','BHS','044'),('BH','BAHRAIN','Bahrain','BHR','048'),('BD','BANGLADESH','Bangladesh','BGD','050'),('BB','BARBADOS','Barbados','BRB','052'),('BY','BELARUS','Belarus','BLR','112'),('BE','BELGIUM','Belgium','BEL','056'),('BZ','BELIZE','Belize','BLZ','084'),('BJ','BENIN','Benin','BEN','204'),('BM','BERMUDA','Bermuda','BMU','060'),('BT','BHUTAN','Bhutan','BTN','064'),('BO','BOLIVIA','Bolivia','BOL','068'),('BA','BOSNIA AND HERZEGOVINA','BosniaAndHerzegovina','BIH','070'),('BW','BOTSWANA','Botswana','BWA','072'),('BR','BRAZIL','Brazil','BRA','076'),('BN','BRUNEI DARUSSALAM','BruneiDarussalam','BRN','096'),('BG','BULGARIA','Bulgaria','BGR','100'),('BF','BURKINA FASO','BurkinaFaso','BFA','854'),('BI','BURUNDI','Burundi','BDI','108'),('KH','CAMBODIA','Cambodia','KHM','116'),('CM','CAMEROON','Cameroon','CMR','120'),('CA','CANADA','Canada','CAN','124'),('CV','CAPE VERDE','CapeVerde','CPV','132'),('KY','CAYMAN ISLANDS','CaymanIslands','CYM','136'),('CF','CENTRAL AFRICAN REPUBLIC','CentralAfricanRepublic','CAF','140'),('TD','CHAD','Chad','TCD','148'),('CL','CHILE','Chile','CHL','152'),('CN','CHINA','China','CHN','156'),('CO','COLOMBIA','Colombia','COL','170'),('KM','COMOROS','Comoros','COM','174'),('CG','CONGO','Congo','COG','178'),('CD','CONGO, THE DEMOCRATIC REPUBLIC OF THE','CongoDemo','COD','180'),('CK','COOK ISLANDS','CookIslands','COK','184'),('CR','COSTA RICA','CostaRica','CRI','188'),('CI','COTE D\'IVOIRE','CoteIvoire','CIV','384'),('HR','CROATIA','Croatia','HRV','191'),('CU','CUBA','Cuba','CUB','192'),('CY','CYPRUS','Cyprus','CYP','196'),('CZ','CZECH REPUBLIC','CzechRepublic','CZE','203'),('DK','DENMARK','Denmark','DNK','208'),('DJ','DJIBOUTI','Djibouti','DJI','262'),('DM','DOMINICA','Dominica','DMA','212'),('DO','DOMINICAN REPUBLIC','DominicanRepublic','DOM','214'),('EC','ECUADOR','Ecuador','ECU','218'),('EG','EGYPT','Egypt','EGY','818'),('SV','EL SALVADOR','ElSalvador','SLV','222'),('GQ','EQUATORIAL GUINEA','EquatorialGuinea','GNQ','226'),('ER','ERITREA','Eritrea','ERI','232'),('EE','ESTONIA','Estonia','EST','233'),('ET','ETHIOPIA','Ethiopia','ETH','231'),('FK','FALKLAND ISLANDS (MALVINAS)','FalklandIslands','FLK','238'),('FO','FAROE ISLANDS','FaroeIslands','FRO','234'),('FJ','FIJI','Fiji','FJI','242'),('FI','FINLAND','Finland','FIN','246'),('FR','FRANCE','France','FRA','250'),('GF','FRENCH GUIANA','FrenchGuiana','GUF','254'),('PF','FRENCH POLYNESIA','FrenchPolynesia','PYF','258'),('GA','GABON','Gabon','GAB','266'),('GM','GAMBIA','Gambia','GMB','270'),('GE','GEORGIA','Georgia','GEO','268'),('DE','GERMANY','Germany','DEU','276'),('GH','GHANA','Ghana','GHA','288'),('GI','GIBRALTAR','Gibraltar','GIB','292'),('GR','GREECE','Greece','GRC','300'),('GL','GREENLAND','Greenland','GRL','304'),('GD','GRENADA','Grenada','GRD','308'),('GP','GUADELOUPE','Guadeloupe','GLP','312'),('GU','GUAM','Guam','GUM','316'),('GT','GUATEMALA','Guatemala','GTM','320'),('GN','GUINEA','Guinea','GIN','324'),('GW','GUINEA-BISSAU','GuineaBissau','GNB','624'),('GY','GUYANA','Guyana','GUY','328'),('HT','HAITI','Haiti','HTI','332'),('VA','HOLY SEE (VATICAN CITY STATE)','HolySee','VAT','336'),('HN','HONDURAS','Honduras','HND','340'),('HK','HONG KONG','HongKong','HKG','344'),('HU','HUNGARY','Hungary','HUN','348'),('IS','ICELAND','Iceland','ISL','352'),('IN','INDIA','India','IND','356'),('ID','INDONESIA','Indonesia','IDN','360'),('IR','IRAN, ISLAMIC REPUBLIC OF','Iran','IRN','364'),('IQ','IRAQ','Iraq','IRQ','368'),('IE','IRELAND','Ireland','IRL','372'),('IL','ISRAEL','Israel','ISR','376'),('IT','ITALY','Italy','ITA','380'),('JM','JAMAICA','Jamaica','JAM','388'),('JP','JAPAN','Japan','JPN','392'),('JO','JORDAN','Jordan','JOR','400'),('KZ','KAZAKHSTAN','Kazakhstan','KAZ','398'),('KE','KENYA','Kenya','KEN','404'),('KI','KIRIBATI','Kiribati','KIR','296'),('KP','KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF','KoreaDemo','PRK','408'),('KR','KOREA, REPUBLIC OF','Korea','KOR','410'),('KW','KUWAIT','Kuwait','KWT','414'),('KG','KYRGYZSTAN','Kyrgyzstan','KGZ','417'),('LA','LAO PEOPLE\'S DEMOCRATIC REPUBLIC','Lao','LAO','418'),('LV','LATVIA','Latvia','LVA','428'),('LB','LEBANON','Lebanon','LBN','422'),('LS','LESOTHO','Lesotho','LSO','426'),('LR','LIBERIA','Liberia','LBR','430'),('LY','LIBYAN ARAB JAMAHIRIYA','LibyanArabJamahiriya','LBY','434'),('LI','LIECHTENSTEIN','Liechtenstein','LIE','438'),('LT','LITHUANIA','Lithuania','LTU','440'),('LU','LUXEMBOURG','Luxembourg','LUX','442'),('MO','MACAO','Macao','MAC','446'),('MK','MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','Macedonia','MKD','807'),('MG','MADAGASCAR','Madagascar','MDG','450'),('MW','MALAWI','Malawi','MWI','454'),('MY','MALAYSIA','Malaysia','MYS','458'),('MV','MALDIVES','Maldives','MDV','462'),('ML','MALI','Mali','MLI','466'),('MT','MALTA','Malta','MLT','470'),('MH','MARSHALL ISLANDS','MarshallIslands','MHL','584'),('MQ','MARTINIQUE','Martinique','MTQ','474'),('MR','MAURITANIA','Mauritania','MRT','478'),('MU','MAURITIUS','Mauritius','MUS','480'),('MX','MEXICO','Mexico','MEX','484'),('FM','MICRONESIA, FEDERATED STATES OF','Micronesia','FSM','583'),('MD','MOLDOVA, REPUBLIC OF','Moldova','MDA','498'),('MC','MONACO','Monaco','MCO','492'),('MN','MONGOLIA','Mongolia','MNG','496'),('MS','MONTSERRAT','Montserrat','MSR','500'),('MA','MOROCCO','Morocco','MAR','504'),('MZ','MOZAMBIQUE','Mozambique','MOZ','508'),('MM','MYANMAR','Myanmar','MMR','104'),('NA','NAMIBIA','Namibia','NAM','516'),('NR','NAURU','Nauru','NRU','520'),('NP','NEPAL','Nepal','NPL','524'),('NL','NETHERLANDS','Netherlands','NLD','528'),('AN','NETHERLANDS ANTILLES','NetherlandsAntilles','ANT','530'),('NC','NEW CALEDONIA','NewCaledonia','NCL','540'),('NZ','NEW ZEALAND','NewZealand','NZL','554'),('NI','NICARAGUA','Nicaragua','NIC','558'),('NE','NIGER','Niger','NER','562'),('NG','NIGERIA','Nigeria','NGA','566'),('NU','NIUE','Niue','NIU','570'),('NF','NORFOLK ISLAND','NorfolkIsland','NFK','574'),('MP','NORTHERN MARIANA ISLANDS','NorthernMarianaIslands','MNP','580'),('NO','NORWAY','Norway','NOR','578'),('OM','OMAN','Oman','OMN','512'),('PK','PAKISTAN','Pakistan','PAK','586'),('PW','PALAU','Palau','PLW','585'),('PA','PANAMA','Panama','PAN','591'),('PG','PAPUA NEW GUINEA','PapuaNewGuinea','PNG','598'),('PY','PARAGUAY','Paraguay','PRY','600'),('PE','PERU','Peru','PER','604'),('PH','PHILIPPINES','Philippines','PHL','608'),('PN','PITCAIRN','Pitcairn','PCN','612'),('PL','POLAND','Poland','POL','616'),('PT','PORTUGAL','Portugal','PRT','620'),('PR','PUERTO RICO','PuertoRico','PRI','630'),('QA','QATAR','Qatar','QAT','634'),('RE','REUNION','Reunion','REU','638'),('RO','ROMANIA','Romania','ROM','642'),('RU','RUSSIAN FEDERATION','RussianFederation','RUS','643'),('RW','RWANDA','Rwanda','RWA','646'),('SH','SAINT HELENA','SaintHelena','SHN','654'),('KN','SAINT KITTS AND NEVIS','SaintKittsAndNevis','KNA','659'),('LC','SAINT LUCIA','SaintLucia','LCA','662'),('PM','SAINT PIERRE AND MIQUELON','SaintPierreAndMiquelon','SPM','666'),('VC','SAINT VINCENT AND THE GRENADINES','SaintVincentAndTheGrenadines','VCT','670'),('WS','SAMOA','Samoa','WSM','882'),('SM','SAN MARINO','SanMarino','SMR','674'),('ST','SAO TOME AND PRINCIPE','SaoTomeAndPrincipe','STP','678'),('SA','SAUDI ARABIA','SaudiArabia','SAU','682'),('SN','SENEGAL','Senegal','SEN','686'),('SC','SEYCHELLES','Seychelles','SYC','690'),('SL','SIERRA LEONE','SierraLeone','SLE','694'),('SG','SINGAPORE','Singapore','SGP','702'),('SK','SLOVAKIA','Slovakia','SVK','703'),('SI','SLOVENIA','Slovenia','SVN','705'),('SB','SOLOMON ISLANDS','SolomonIslands','SLB','090'),('SO','SOMALIA','Somalia','SOM','706'),('ZA','SOUTH AFRICA','SouthAfrica','ZAF','710'),('ES','SPAIN','Spain','ESP','724'),('LK','SRI LANKA','SriLanka','LKA','144'),('SD','SUDAN','Sudan','SDN','736'),('SR','SURINAME','Suriname','SUR','740'),('SJ','SVALBARD AND JAN MAYEN','SvalbardAndJanMayen','SJM','744'),('SZ','SWAZILAND','Swaziland','SWZ','748'),('SE','SWEDEN','Sweden','SWE','752'),('CH','SWITZERLAND','Switzerland','CHE','756'),('SY','SYRIAN ARAB REPUBLIC','SyrianArabRepublic','SYR','760'),('TW','TAIWAN, PROVINCE OF CHINA','Taiwan','TWN','158'),('TJ','TAJIKISTAN','Tajikistan','TJK','762'),('TZ','TANZANIA, UNITED REPUBLIC OF','Tanzania','TZA','834'),('TH','THAILAND','Thailand','THA','764'),('TG','TOGO','Togo','TGO','768'),('TK','TOKELAU','Tokelau','TKL','772'),('TO','TONGA','Tonga','TON','776'),('TT','TRINIDAD AND TOBAGO','TrinidadAndTobago','TTO','780'),('TN','TUNISIA','Tunisia','TUN','788'),('TR','TURKEY','Turkey','TUR','792'),('TM','TURKMENISTAN','Turkmenistan','TKM','795'),('TC','TURKS AND CAICOS ISLANDS','TurksAndCaicosIslands','TCA','796'),('TV','TUVALU','Tuvalu','TUV','798'),('UG','UGANDA','Uganda','UGA','800'),('UA','UKRAINE','Ukraine','UKR','804'),('AE','UNITED ARAB EMIRATES','UnitedArabEmirates','ARE','784'),('GB','UNITED KINGDOM','UnitedKingdom','GBR','826'),('US','UNITED STATES','UnitedStates','USA','840'),('UY','URUGUAY','Uruguay','URY','858'),('UZ','UZBEKISTAN','Uzbekistan','UZB','860'),('VU','VANUATU','Vanuatu','VUT','548'),('VE','VENEZUELA','Venezuela','VEN','862'),('VN','VIET NAM','VietNam','VNM','704'),('VG','VIRGIN ISLANDS, BRITISH','VirginIslandsBritish','VGB','092'),('VI','VIRGIN ISLANDS, U.S.','VirginIslandsUs','VIR','850'),('WF','WALLIS AND FUTUNA','WallisAndFutuna','WLF','876'),('EH','WESTERN SAHARA','WesternSahara','ESH','732'),('YE','YEMEN','Yemen','YEM','887'),('ZM','ZAMBIA','Zambia','ZMB','894'),('ZW','ZIMBABWE','Zimbabwe','ZWE','716');
CREATE TABLE payer_user (id int(11) NOT NULL AUTO_INCREMENT,firstname varchar(200) NOT NULL,lastname varchar(200) DEFAULT NULL,email varchar(200) DEFAULT NULL,street_number text,street text,zipcode varchar(20) DEFAULT NULL,city varchar(200) DEFAULT NULL,country varchar(200) DEFAULT NULL,student_id int(11) NOT NULL DEFAULT '0',company varchar(200) DEFAULT NULL,vat_number varchar(20) DEFAULT NULL,phone varchar(50) DEFAULT NULL,civility varchar(20) DEFAULT NULL,PRIMARY KEY (id));
CREATE TABLE payment_atos (  id int(11) NOT NULL AUTO_INCREMENT,  user_id int(11) NOT NULL DEFAULT '0',  sess_id int(11) NOT NULL DEFAULT '0',  pay_type int(11) NOT NULL DEFAULT '0',  pay_data text,  pay_time int(11) NOT NULL DEFAULT '0',  status int(11) NOT NULL DEFAULT '0',  curr_quota int(11) NOT NULL DEFAULT '0',  transaction_id int(11) DEFAULT NULL,  PRIMARY KEY (id));
CREATE TABLE payment_log (  id int(11) NOT NULL AUTO_INCREMENT,  user_id int(11) NOT NULL DEFAULT '0',  sess_id int(11) NOT NULL DEFAULT '0',  pay_type int(11) NOT NULL DEFAULT '0',  pay_data text,  pay_time int(11) NOT NULL DEFAULT '0',  status int(11) NOT NULL DEFAULT '0',  curr_quota int(11) NOT NULL DEFAULT '0',  PRIMARY KEY (id));
INSERT INTO settings_current (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable) VALUES ('show_emailtemplates', NULL, 'radio', 'Advanced', 'true', 'ShowEmailTemplatesTitle', 'ShowEmailTemplatesComment', NULL, NULL, 1);
INSERT INTO settings_options (variable, value, display_text) VALUES ('show_emailtemplates', 'true', 'Yes');
INSERT INTO settings_options (variable, value, display_text) VALUES ('show_emailtemplates', 'false', 'No');
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'account_valid_duration';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'administratorTelephone';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'advanced_filemanager';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'allow_course_theme';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'allow_group_categories';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'allow_message_tool';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'allow_reservation';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'allow_user_edit_agenda';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'allow_user_headings';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'allow_use_sub_language';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'automatic_group_filling';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'breadcrumbs_course_homepage';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'calendar_detail_view';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'calendar_export_all';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'calendar_navigation';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'calendar_types';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'calendar_types';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'calendar_types';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'captcha';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'create_new_group';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'display_breadcrumbs';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'display_categories_on_homepage';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'display_context_help';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'display_coursecode_in_courselist';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'display_feedback_messages';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'display_mini_month_calendar';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'display_platform_header_in_course';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'display_teacher_in_courselist';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'display_upcoming_events';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'dropbox_allow_group';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'dropbox_allow_just_upload';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'dropbox_allow_mailing';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'dropbox_allow_overwrite';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'dropbox_allow_student_to_student';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'dropbox_max_filesize';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'example_material_course_creation';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'extendedprofile_registration';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'extendedprofile_registration';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'extendedprofile_registration';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'extendedprofile_registration';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'extendedprofile_registrationrequired';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'extendedprofile_registrationrequired';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'extendedprofile_registrationrequired';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'extendedprofile_registrationrequired';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'extended_profile';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'groupscenariofield';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'hide_dltt_markup';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'homepage_view';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'installation_date';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'math_mimetex';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'message_max_upload_filesize';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'mindmap_converter_activated';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'new_group_seats';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'noreply_email_address';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'number_of_announcements';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'number_of_upcoming_events';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'page_after_login';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'platform_charset';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'portal_view';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'server_type';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'showonline';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'showonline';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'showonline';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_back_link_on_top_of_tree';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_closed_courses';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_different_course_language';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_email_addresses';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_empty_course_categories';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_glossary_in_documents';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_glossary_in_extra_tools';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_navigation_menu';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_number_of_courses';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_quizcategory';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_session_data';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'show_toolshortcuts';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'split_users_upload_directory';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'students_download_folders';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'student_view_enabled';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'time_limit_whosonline';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'user_manage_group_agenda';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'user_selected_theme';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'youtube_for_students';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'gradebook_enable';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'gradebook_score_display_coloring';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'gradebook_score_display_colorsplit';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'gradebook_score_display_custom';
UPDATE settings_current SET category = 'Advanced' WHERE variable = 'gradebook_score_display_upperlimit';
UPDATE settings_current SET selected_value = 'dokeos2_blue_tablet' WHERE variable='stylesheets';
CREATE TABLE certificate (id INT NOT NULL AUTO_INCREMENT, portal_name VARCHAR(200), portal_logo VARCHAR(200), company VARCHAR(200), company_logo VARCHAR(200), certificate_date DATE NOT NULL DEFAULT '0000-00-00', message TEXT, company_seal VARCHAR(200), scope TEXT, display_as VARCHAR(20) NOT NULL DEFAULT 'html', template INT NOT NULL DEFAULT 1, required_score FLOAT NOT NULL DEFAULT 0, PRIMARY KEY (id));
CREATE TABLE certificate_template (id INT NOT NULL AUTO_INCREMENT, title VARCHAR(200) , description TEXT , thumbnail VARCHAR(200) , content LONGTEXT , position INT NOT NULL DEFAULT 0, creation_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (id));
CREATE TABLE IF NOT EXISTS email_template (id int UNSIGNED NOT NULL auto_increment, title varchar(250) NOT NULL, description text NOT NULL, image varchar(250) NOT NULL, language varchar(250) NOT NULL, content text NOT NULL, PRIMARY KEY  (id));
-------------------------------------------------------------------------------------------
-- xxSTATSxx
-------------------------------------------------------------------------------------------
-- xxUSERxx
-------------------------------------------------------------------------------------------
-- xxCOURSExx
ALTER TABLE `quiz_answer` CHANGE `hotspot_type` `hotspot_type` ENUM( 'square', 'circle', 'poly', 'delineation', 'oar' ) NULL DEFAULT NULL;
ALTER TABLE quiz_question ADD COLUMN media_position varchar(50) NOT NULL default 'right';
ALTER TABLE student_publication ADD COLUMN remark text;
ALTER TABLE quiz_type ADD COLUMN current_active mediumint unsigned NOT NULL default '0';
ALTER TABLE quiz_type ADD COLUMN scenario_type mediumint unsigned NOT NULL default '1';
ALTER TABLE dropbox_file ADD COLUMN type int DEFAULT 0;