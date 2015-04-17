<?php
require_once '../../inc/global.inc.php';

// only for admin account
api_protect_admin_script();

$tbl_invoice = Database::get_main_table(TABLE_MAIN_INVOICE);
$settings_current = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
$settings_options = Database::get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);

echo '<p>alter invoice table...</p>';

/* Invoice table */
$sql = "DROP TABLE IF EXISTS {$tbl_invoice};";
Database::query($sql, __FILE__, __LINE__);
$sql = "CREATE TABLE  {$tbl_invoice} (`id` int(11) NOT NULL AUTO_INCREMENT,`user_id` int(10) unsigned NOT NULL,`invoice` varchar(255) NOT NULL,`full_path` varchar(255) NOT NULL, `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`));";
Database::query($sql, __FILE__, __LINE__);

echo '<p>Done.</p>';

echo '<p>alter settings_current and setting_options tables...</p>';

/* settings_current and setting_options tables */

// variable ImageSite
$sql = "SELECT * FROM $settings_current WHERE variable = 'ImageSite'";
$res = Database::query($sql);
if (Database::num_rows($res)<=0) {
    $sql = "INSERT INTO $settings_current (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable) VALUES ('ImageSite', NULL, 'file', 'Platform', 'ImageSite.jpg', 'ImageSiteTitle', 'ImageSiteComment', NULL, NULL, 1)";
    Database::query($sql);
}

// Variable e_commerce_catalog_tax
$res = Database::query("SELECT * FROM $settings_current WHERE variable = 'e_commerce_catalog_tax'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_current (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable) VALUES
                        ('e_commerce_catalog_tax', NULL, 'textfield', 'Ecommerce', '0', 'EcommerceTaxTitle', 'EcommerceTaxPercent', NULL, NULL, 1);
                    ");
}

// Variable e_commerce_catalog_decimal
$res = Database::query("SELECT * FROM $settings_current WHERE variable = 'e_commerce_catalog_decimal'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_current (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable) VALUES
                    ('e_commerce_catalog_decimal', NULL, 'radio', 'Ecommerce', 1, 'EcommerceDecimalTitle', 'EcommerceDecimalSign', NULL, NULL, 1);
                ");
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES
                        ('e_commerce_catalog_decimal','1','Comma'),
                        ('e_commerce_catalog_decimal','2','Dot')
                    ");
}

// Variable display_catalog_on_homepage
$res = Database::query("SELECT * FROM $settings_current WHERE variable = 'display_catalog_on_homepage'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_current (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable) VALUES
                    ('display_catalog_on_homepage', NULL, 'radio', 'Advanced', 'true', 'DisplayCatalogOnHomeTitle', 'DisplayCatalogOnHomeComment', NULL, NULL, 1);
                ");
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES
                        ('display_catalog_on_homepage','false','No'),
                        ('display_catalog_on_homepage','true','Yes')
                   ");
}

// Variable terms_and_conditions
$res = Database::query("SELECT * FROM $settings_current WHERE variable = 'terms_and_conditions'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_current (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable) VALUES
                    ('terms_and_conditions', NULL, 'textfield', 'Advanced', '', 'TermsAndConditionsTitle', 'TermsAndConditionsComment', NULL, NULL, 1);
                ");
}

// Variable catalogName
$res = Database::query("SELECT * FROM $settings_options WHERE variable = 'catalogName'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES ('catalogName', '', 'catalogName');");
}

// Variable categoryName
$res = Database::query("SELECT * FROM $settings_options WHERE variable = 'categoryName'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES ('categoryName', '', 'categoryName');");
}

// Variable invoiceLogo
$res = Database::query("SELECT * FROM $settings_options WHERE variable = 'invoiceLogo'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES ('invoiceLogo', '', 'invoiceLogo');");
}

// Variable companyAddress
$res = Database::query("SELECT * FROM $settings_options WHERE variable = 'companyAddress'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES ('companyAddress', '', 'companyAddress');");
}

// Variable invoiceBank
$res = Database::query("SELECT * FROM $settings_options WHERE variable = 'invoiceBank'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES ('invoiceBank', '', 'invoiceBank');");
}

// Variable messageCreditcard
$res = Database::query("SELECT * FROM $settings_options WHERE variable = 'messageCreditcard'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES ('messageCreditcard', '', 'messageCreditcard');");
}

// Variable messageCheque
$res = Database::query("SELECT * FROM $settings_options WHERE variable = 'messageCheque'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES ('messageCheque', '', 'messageCheque');");
}

// Variable messageEndPayment
$res = Database::query("SELECT * FROM $settings_options WHERE variable = 'messageEndPayment'");
if (Database::num_rows($res) <= 0) {
    Database::query("INSERT INTO $settings_options(variable, value, display_text) VALUES ('messageEndPayment', '', 'messageEndPayment');");
}

echo '<p>Done.</p>';

echo '<p>Alter session table...</p>';
/* session table */
$tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);

// it adds "duration", "duration_type", "image
Database::query("ALTER TABLE $tbl_session 
                    ADD COLUMN `duration` INT  NOT NULL DEFAULT 1, 
                    ADD COLUMN `duration_type` SET ('day','week', 'month')  NOT NULL DEFAULT 'day', 
                    ADD COLUMN `image` varchar(255) NOT NULL DEFAULT 'default-sessions.png';");

echo '<p>Done.</p>';

echo '<p>Alter course_rel_user table...</p>';
/* course_rel_user table */
$tbl_cours_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);

// It adds date_start, date_end fields
Database::query("ALTER TABLE $tbl_cours_user 
                    ADD COLUMN `date_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                    ADD COLUMN `date_end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';");

echo '<p>Done.</p>';

echo '<p>Alter commerce_items table...</p>';
/* commerce_items table */
$tbl_commerce_items = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);

// It adds id_category, duration, duration_type, image, description, id_category, id_session, cost_ttc, chr_type_cost, sort fields
Database::query("ALTER TABLE $tbl_commerce_items 
                    ADD COLUMN id_category INT(11) NULL ,
                    ADD COLUMN duration INT NOT NULL DEFAULT '1',
                    ADD COLUMN duration_type set('day','week','month') NOT NULL DEFAULT 'day',
                    ADD COLUMN image varchar(255) NOT NULL,
                    ADD COLUMN description text NOT NULL,
                    ADD COLUMN id_category INT(11) DEFAULT NULL,
                    ADD COLUMN id_session INT(11) DEFAULT NULL,
                    ADD COLUMN cost_ttc decimal(10,2) DEFAULT '0.00',
                    ADD COLUMN chr_type_cost varchar(45) DEFAULT NULL,
                    ADD COLUMN sort INT(11) NOT NULL DEFAULT '0';");

echo '<p>Done.</p>';

echo '<p>Alter ecommerce_category table...</p>';
/* ecommerce_category table */
$tbl_commerce_category = Database::get_main_table(TABLE_MAIN_ECOMMERCE_CATEGORY);

// Create the table
Database::query("CREATE TABLE $tbl_commerce_category (
                    id_category INT(11) NOT NULL AUTO_INCREMENT,
                    chr_category VARCHAR(100) DEFAULT NULL,
                    bool_active TINYINT(4) DEFAULT '1',
                    chr_language VARCHAR(45) DEFAULT NULL,
                    PRIMARY KEY (id_category)
                );");
 
echo '<p>Done.</p>';
?>
