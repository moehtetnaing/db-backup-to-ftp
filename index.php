<?php

/* Set Default */

set_time_limit(0);
require_once 'sent-mail.php';
$to = 'example@example.com';
$db_count 						= -1;
$ftp_count 						= -1;


//----------------------------------------------------------------
//DB credentials
//----------------------------------------------------------------
//Configure credentials of one or more database to backup

//DB 1
$db_count++;
$db[$db_count]['db_user'] 		= "dbuser";
$db[$db_count]['db_password'] 	= "dbpassword";
$db[$db_count]['db_name'] 		= "dbname";
$db[$db_count]['sql_file'] 		= "dump_".date('Y-m-d')."_".time()."_{$db[$db_count]['db_name']}.sql";


/* FTP credentials */
/* Configure credentials of one or more ftp server to transfer the backup */
$ftp_count++;
$ftp[$ftp_count]['ftps'] 				= false;
$ftp[$ftp_count]['ftp_server'] 			= "ftp_server_ip_address";
$ftp[$ftp_count]['ftp_user'] 			= "ftpusername";
$ftp[$ftp_count]['ftp_password'] 		= "ftppassword";
$ftp[$ftp_count]['ftp_passive_mode'] 	= true;
$ftp[$ftp_count]['ftp_remote_folder'] 	= "";	//e.g. /mysite/backups

/* Interate over all databases */
foreach($db as $db_item)
{
	//Create SQL dump and gzip the dumped file
	exec("mysqldump -u {$db_item['db_user']} -p{$db_item['db_password']} --allow-keywords --add-drop-table --complete-insert --hex-blob --quote-names {$db_item['db_name']} > {$db_item['sql_file']}");
	exec("gzip {$db_item['sql_file']}");


	//----------------------------------------------------------------
	//FTP transfer: Transfer sql dump to the configured ftp servers
	//----------------------------------------------------------------

	if($ftp_count >= 0)
	{
		foreach($ftp as $ftp_item)
		{
			//Initiate connection
			if($ftp_item['ftps'])
				$connection_id = ftp_ssl_connect($ftp_item['ftp_server']);
			else
				$connection_id = ftp_connect($ftp_item['ftp_server']);

			if(!$connection_id)
				echo "Error: Can't connect to {$ftp_item['ftp_server']}\n";


			//Login with user and password
			$login_result = ftp_login($connection_id, $ftp_item['ftp_user'], $ftp_item['ftp_password']);

			if(!$login_result)
				echo "Error: Login wrong for {$ftp_item['ftp_server']}\n";


			//Passive mode?
			ftp_pasv($connection_id, $ftp_item['ftp_passive_mode']);

			// Upload file to ftp
			if (!ftp_put($connection_id, $ftp_item['ftp_remote_folder']."/".$db_item['sql_file'].'.gz', $db_item['sql_file'].'.gz', FTP_BINARY))
			{
                $subject = 'Failed Backup Notification';
                $body = "DB Backup Failed!. </br>Error: While uploading {$db_item['sql_file']}.gz to {$ftp_item['ftp_server']}.\n";
                sent_noti_email($to, $subject, $body); // sent failed noti email
				echo "Error: While uploading {$db_item['sql_file']}.gz to {$ftp_item['ftp_server']}.\n";
			} else {
                $subject = 'Successful Backup Notification';
                $body = 'DB Backup Successfully!.';
                sent_noti_email($to, $subject, $body); // sent successful noti email
            }

			//Close ftp connection
			ftp_close($connection_id);
		}
	}

	// Delete original *.sql file
	if(file_exists($db_item['sql_file']))
		unlink($db_item['sql_file']);

    // Delete original *.gz file
    if(file_exists($db_item['sql_file'].'.gz'))
		unlink($db_item['sql_file'].'.gz');
}


?>