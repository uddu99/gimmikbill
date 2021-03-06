<?php
/**
* PHP Mikrotik Billing (www.phpmixbill.com)
* Ismail Marzuqi <iesien22@yahoo.com>
* @version		5.0
* @copyright	Copyright (C) 2014-2015 PHP Mikrotik Billing
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @donate		PayPal: iesien22@yahoo.com / Bank Mandiri: 130.00.1024957.4
**/
_admin();
$ui->assign('_title', $_L['Front_end_title'].' - '. $config['CompanyName']);
$ui->assign('_system_menu', 'frontend');

$action = $routes['1'];
$admin = Admin::_info();
$ui->assign('_admin', $admin);

if($admin['user_type'] != 'Admin' AND $admin['user_type'] != 'Sales'){
	r2(U."dashboard",'e',$_L['Do_Not_Access']);
}

use PEAR2\Net\RouterOS;
require_once 'system/autoload/PEAR2/Autoload.php';

switch ($action) {
		
	case 'frontend':
		$ui->assign('xfooter', '<script type="text/javascript" src="ui/lib/c/front-end.js"></script>');
		$name = _post('name');
		if ($name != ''){
			$paginator = Paginator::bootstrap('tbl_frontend','title_msg','%'.$name.'%','status','Y');
			$d = ORM::for_table('tbl_frontend')->where('tbl_frontend.status','Y')->where_like('tbl_frontend.title_msg','%'.$name.'%')->offset($paginator['startpoint'])->limit($paginator['limit'])->find_many();
		}else{
			$paginator = Paginator::bootstrap('tbl_frontend','status','Y');
			$d = ORM::for_table('tbl_frontend')->where('tbl_frontend.status','Y')->offset($paginator['startpoint'])->limit($paginator['limit'])->find_many();
		}
	
		$ui->assign('d',$d);
		$ui->assign('paginator',$paginator);
        $ui->display('frontend-init.tpl');
        break;
	case 'add':
		
        $ui->display('frontend-add.tpl');
        break;
	case 'addfrontend':
		
		try{
			$title_msg = _post('title_msg');
			$logo_img = _file('logo_img');
			$bg_img = _file('bg_img');
			_log($title_msg ,$admin['username']);
			$msg = '';
			if ($title_msg == '' OR $logo_img == '' OR $bg_img == ''){
				$msg .= $_L['All_field_is_required']. '<br>';
			}
		}
		catch(Exception $e)
		{
			$msg = $e->getMessage();
		}

		if($msg == ''){
			$d = ORM::for_table('tbl_frontend')->create();
			$d->title_msg = $title_msg;
			$d->logo_img = $logo_img;
			$d->bg_img = $bg_img;
			$d->cb = $admin['username'];
			$d->save();

			r2(U . 'frontend/frontend', 's', $_L['Created_Successfully']);
		}
		else{
			r2(U . 'frontend/add', 'e', $msg);
		}

        break;
	case 'edit':
        $id  = $routes['2'];
		_log( 'Edit id : '.$id ,$admin['username']);
        $d = ORM::for_table('tbl_frontend')->find_one($id);
		//_log( 'Edit object : '.$d ,$admin['username']);
        if($d){
            $ui->assign('d',$d);
			
            $ui->display('frontend-edit.tpl');
        }else{
            r2(U . 'frontend/frontend', 'e', $_L['Frontend_Not_Found']);
        }
        break;

    case 'editfrontend':

		$id = _post('id');
		
		try{
			$title_msg = _post('title_msg');
			_log('Check file upload > '.empty($_FILES['logo_img']['tmp_name']),$admin['username']);
			if (!empty($_FILES['logo_img']['tmp_name']))
				$logo_img = _file('logo_img');
			if (!empty($_FILES['bg_img']['tmp_name']))
				$bg_img = _file('bg_img');
			
			$msg = '';
			if ($title_msg == '' ){
				$msg .= $_L['Title_msg_field_is_required']. '<br>';
			}
		}
		catch(Exception $e)
		{
			$msg = $e->getMessage();
		}

		$d = ORM::for_table('tbl_frontend')->find_one($id);
        if($d){
        }else{
            $msg .= $_L['Data_Not_Found']. '<br>';
        }

		if($msg == ''){
			if (!empty($title_msg)) {
				$d->title_msg = $title_msg;
			} else {
				$d->title_msg = $d->title_msg;
			}
			if (!empty($logo_img)){
				$d->logo_img = $logo_img;
			} else {
				$d->logo_img = $d->logo_img;
			}
			if (!empty($bg_img)){
				$d->bg_img = $bg_img;
			} else {
				$d->bg_img = $d->bg_img;
			}
			_log('logo img > '.$d->logo_img,$admin['username']);
			_log('bg img > '.$d->bg_img,$admin['username']);
			$date = new DateTime();
			$d->md = $date->getTimestamp();
			$d->mb = $admin['username'];
			$d->save();

			r2(U . 'frontend/frontend', 's', $_L['Created_Successfully']);
		}
		else{
			r2(U . 'frontend/edit/'.$id , 'e', $msg);
		}

        break;
	case 'delete':
        $id  = $routes['2'];
		
        $d = ORM::for_table('tbl_frontend')->find_one($id);
        if($d){
			
            $d->delete();
			
            r2(U . 'frontend/frontend', 's', $_L['Delete_Successfully']);
        }
        break;

	case 'ftp':
        $id  = $routes['2'];
		
        //$d = ORM::for_table('tbl_frontend')->join('tbl_login_template', array('tbl_frontend.template_id', '=', 'tbl_login_template.id'))->where('tbl_frontend.id',$id)->find_many();
		$d = ORM::for_table('tbl_frontend')->find_one($id);

        if($d){
			
			$r = ORM::for_table('tbl_routers')->find_one();

			$mikrotik = Router::_info($r['name']);

			$src_file = 'template/login.html';
			$dest_file =  '/hotspot/login.html';
			$remote_dir = '/hotspot/';
			$remote_file = 'login.html';
			_log( '@Is file >> '. file_exists( $src_file ) , $admin['username']);

			//FTP version
			// set up basic connection
			
			/*$conn_id = ftp_connect( $mikrotik['ip_address'] );
			_log( '@conn_id >> '. $conn_id , $admin['username']);
			
			// login with username and password
			if ( ftp_login( $conn_id , $mikrotik['username'] , $mikrotik['password'] ) ) {

				ftp_chdir( $conn_id , $remote_dir );
				ftp_pasv ( $conn_id , true );

				_log( '@login_result >> '. $login_result  , $admin['username']);

				// upload a file
				if ( ftp_nb_put( $conn_id, $remote_file, $src_file , FTP_ASCII ) ) {
					r2(U . 'frontend/frontend' , 's', $_L['Upload_Successfully']);
				} else {
					r2(U . 'frontend/frontend' , 'e', $_L['Upload_Failed'] );
				}

			}
			else {
				r2(U . 'frontend/frontend' , 'e' , $_L['Unable_to_authenticate_with_server'] );
			}

			// close the connection
			ftp_close($conn_id);
			*/


			//SFTP version
			/*
			set_include_path( get_include_path() . PATH_SEPARATOR . 'phpseclib1.0.3' );

  			include('Net/SFTP.php');

			try{
				$local_directory = $src_file;
				$remote_directory = $dest_file;

				// Add the correct FTP credentials below 
				_log( '@00000 >> ' , $admin['username']);
				$sftp = new Net_SFTP( $mikrotik['ip_address']  );
				_log( '@sftp >> '. $sftp  , $admin['username']);
				if ( !$sftp->login( $mikrotik['username'] , $mikrotik['password'] ) ) 
				{
					r2(U . 'frontend/frontend' , 'e' , $_L['Unable_to_authenticate_with_server'] );
				} 
				_log( '@1111 >> ' , $admin['username']);
				if ( $sftp->put($remote_directory , 
									$local_directory , 
									NET_SFTP_LOCAL_FILE) ) {
					_log( '@2222 >> ' , $admin['username']);
					r2(U . 'frontend/frontend' , 's', $_L['Upload_Successfully']);
				}
				else {
					r2(U . 'frontend/frontend' , 'e', $_L['Upload_Failed'] );
				}
			}
			catch(Exception $ex)
			{
				r2(U . 'frontend/frontend' , 'e', $_L['Upload_Failed'].' - '.$ex->getMessage() );
			}
			*/

			/*
			try{
				_log( '@00000 >> '.$mikrotik['ip_address'] , $admin['username']);
				$connection = ssh2_connect( $mikrotik['ip_address'] , '22' );
				
				_log( '@111 connection >> '.$connection , $admin['username']);
				if (ssh2_auth_password( $connection , $mikrotik['username'] , $mikrotik['password'] )) {
					// initialize sftp
					$sftp = ssh2_sftp($connection);
					_log( '@222 sftp >> '.$sftp , $admin['username']);
					$contents = file_get_contents($src_file);
					_log( '@333 contents >> '.$contents , $admin['username']);
					file_put_contents("ssh2.sftp://{$sftp}/{$src_file}", $contents);
					r2(U . 'frontend/frontend' , 's', $_L['Upload_Successfully']);
				} else {
					r2(U . 'frontend/frontend' , 'e', $_L['Upload_Failed'] );
				}
				_log( '@44444 >> ' , $admin['username']);
				
				r2(U . 'frontend/frontend' , 'e', $_L['Upload_Failed'] );
			}
			catch(Exception $ex) {
				r2(U . 'frontend/frontend' , 'e', $_L['Upload_Failed'].' - '.$ex->getMessage() );
			}
			*/

			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($src_file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($src_file));
			ob_clean();
			flush();
			readfile($src_file);
			//exit;
        }
        break;

    default:
        $ui->display('404.tpl');
}