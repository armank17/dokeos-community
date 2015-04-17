<?php
require_once '../../inc/global.inc.php';

// only for admin account
api_protect_admin_script();

       // User table
        $tbl_users = Database::get_main_table(TABLE_MAIN_USER);
        $sql = "DESC $tbl_users";
        // Possible missing fields
        $missing_fields = array('default_enrolment');
        // Execute this command if the field does not exists
        $command_field['default_enrolment'] = "ALTER TABLE $tbl_users ADD COLUMN default_enrolment int NOT NULL default '0'";
        echo '<pre>';
        echo "<strong>Table : ".$tbl_users."</strong><br/>";
        // Get the current quiz table in an array
        $document_tables = array();
        $rs = Database::query($sql);
        while ($row = Database::fetch_array($rs, 'ASSOC')) {
            var_dump($row['Field']);
            $document_tables[] = $row['Field'];
        }
        foreach ($missing_fields as $missing_field) {
                if (!in_array($missing_field, $document_tables)) {
                    echo "<p style='color:red;'> <strong>Execute :</strong> ".$command_field[$missing_field]."</p></br>";
                    Database::query($command_field[$missing_field]);
                    $affected_lp_rows++;
                }
        }
        echo "</br>";
        // End User table

       // Session table
        $tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
        $sql = "DESC $tbl_session";
        // Possible missing fields
        $missing_fields = array('certif_template','certif_tool','certif_min_score','certif_min_progress');
        // Execute this command if the field does not exists
        $command_field['certif_template'] = "ALTER TABLE $tbl_session ADD COLUMN certif_template int(11) NOT NULL DEFAULT '1'";
        $command_field['certif_tool'] = "ALTER TABLE $tbl_session ADD COLUMN certif_tool varchar(50) NOT NULL DEFAULT 'quiz'";
        $command_field['certif_min_score'] = "ALTER TABLE $tbl_session ADD COLUMN certif_min_score float(6,2) NOT NULL DEFAULT '50.00'";
        $command_field['certif_min_progress'] = "ALTER TABLE $tbl_session ADD COLUMN certif_min_progress float(6,2) NOT NULL DEFAULT '50.00'";
        echo '<pre>';
        echo "<strong>Table : ".$tbl_session."</strong><br/>";
        // Get the current quiz table in an array
        $document_tables = array();
        $rs = Database::query($sql);
        while ($row = Database::fetch_array($rs, 'ASSOC')) {
            var_dump($row['Field']);
            $document_tables[] = $row['Field'];
        }
        foreach ($missing_fields as $missing_field) {
                if (!in_array($missing_field, $document_tables)) {
                    echo "<p style='color:red;'> <strong>Execute :</strong> ".$command_field[$missing_field]."</p></br>";
                    Database::query($command_field[$missing_field]);
                    $affected_lp_rows++;
                }
        }
        echo "</br>";
        // End Session table

       // Session table
        $tbl_session_user = Database::get_main_table(TABLE_MAIN_SESSION_USER);
        $sql = "DESC $tbl_session_user";
        // Possible missing fields
        $missing_fields = array('status');
        // Execute this command if the field does not exists
        $command_field['status'] = "ALTER TABLE $tbl_session_user ADD COLUMN status varchar(4) default NULL";
        echo '<pre>';
        echo "<strong>Table : ".$tbl_session_user."</strong><br/>";
        // Get the current quiz table in an array
        $document_tables = array();
        $rs = Database::query($sql);
        while ($row = Database::fetch_array($rs, 'ASSOC')) {
            var_dump($row['Field']);
            $document_tables[] = $row['Field'];
        }
        foreach ($missing_fields as $missing_field) {
                if (!in_array($missing_field, $document_tables)) {
                    echo "<p style='color:red;'> <strong>Execute :</strong> ".$command_field[$missing_field]."</p></br>";
                    Database::query($command_field[$missing_field]);
                    $affected_lp_rows++;
                }
        }
        echo "</br>";
        // End Session table
        
        // Add dokeos settings if does not exists into database
        $table_settings = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
        $table_settings_options = Database::get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);

        $sql_check = "SELECT id FROM $table_settings WHERE variable='enable_platform_chat'";
        $rs_check = Database::query($sql_check, __FILE__,__LINE__);
        if (Database::num_rows($rs_check) == 0) {
            // Adding the setting
            $sql = "INSERT INTO ".$table_settings." (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable)
                            VALUES ('enable_platform_chat', NULL, 'radio', 'Platform', 'true', 'EnablePlatformChatTitle', 'EnablePlatformChatComment', '0', NULL, 1)";
            Database::query($sql, __FILE__, __LINE__);

            // Adding the options
            $sql = "INSERT INTO ".$table_settings_options." (variable, value, display_text) VALUES
            ('enable_platform_chat', 'true', 'EnablePlatformChat'),
            ('enable_platform_chat', 'false', 'DisablePlatformChat')";
            Database::query($sql, __FILE__, __LINE__);
        }

        $sql_check = "SELECT id FROM $table_settings WHERE variable='platform_chat_request'";
        $rs_check = Database::query($sql_check, __FILE__,__LINE__);
        if (Database::num_rows($rs_check) == 0) {
            // Adding the setting
            $sql = "INSERT INTO ".$table_settings." (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable)
                            VALUES ('platform_chat_request', NULL, 'textfield', 'Platform', '2', 'PlatformChatRequestTitle', 'PlatformChatRequestComment', NULL, NULL, 1)";
            Database::query($sql, __FILE__, __LINE__);

        }