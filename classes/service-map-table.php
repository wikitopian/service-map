<?php

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

global $wpdb;

class Service_Map_Table extends WP_List_Table {
	public $found_data = array();

	public $client_data = array(
		
		);

	public function __construct() {
		global $status, $page;

		parent::__construct(array(
			'singular'=>__('client', 'clientlisttable'),
			'plural'=>__('clients', 'clientlisttable'),
			'ajax'=>true
			));
		
		add_action('admin_head', array(&$this, 'admin_header'));
		}

	function geocode($address) {
    $address = urlencode($address);
    
    $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address={$address}";
    
    $resp_json = file_get_contents($url);
    
    $resp = json_decode($resp_json, true);
    
    if($resp['status']=='OK'){
        $lati = $resp['results'][0]['geometry']['location']['lat'];
        $longi = $resp['results'][0]['geometry']['location']['lng'];
        $formatted_address = $resp['results'][0]['formatted_address'];
         
        if($lati && $longi && $formatted_address){
            $data_arr = array();            
             
            array_push(
                $data_arr, 
                    $lati, 
                    $longi, 
                    $formatted_address
                );
             
            return $data_arr;
			} else {
            return false;
			}
        } else {
        return false;
		}
	}

function admin_header() {
	$page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;

	if ('service-map-manage' !== $page)
		return;

	echo '<style type="text/css">';
	echo '.wp-list-table .column-id { width:5%; }';
	echo '.wp-list-table .column-clientname { width:40%; }';
	echo '.wp-list-table .column-clientaddress { width:35%; }';
	echo '.wp-list-table .column-clientzip { width:20%; }';
	echo '</style>';
	}

function no_items() {
	_e('No clients found');
	}

function column_default($item, $column_name) {
	switch($column_name) {
		case 'clientname':
		case 'clientaddress':
		case 'clientzip':
			return $item[$column_name];
		
		default:
			return print_r($item, true);
		}
	}

function get_columns() {
	$columns = array(
		'cb'=>'<input type="checkbox"/>',
		'clientname'=>__('Client Name', 'clientlisttable'),
		'clientaddress'=>__('Location', 'clientlisttable'),
		'clientzip'=>__('Zip Code', 'clientlisttable')
		);

	return $columns;
	}

function get_sortable_columns() {
	$sortable_columns = array(
		'clientname'=>array('clientname', false),
		'clientaddress'=>array('clientaddress', false),
		'clientzip'=>array('clientzip', false)
		);

	return $sortable_columns;
	}

function usort_reorder($a, $b) {
	$orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'clientname';
	$order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
	$result = strcmp($a[$orderby], $b[$orderby]);

	return ($order === 'asc') ? $result : -$result;
	}

function column_clientname($item) {
	$actions = array(
		'edit'=>sprintf('<a href="?page=%s&action=%s&client=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['ID']),
		'delete'=>sprintf('<a href="?page=%s&action=%s&client=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID']),
		);

	return sprintf('%1$s %2$s', $item['clientname'], $this->row_actions($actions));
	}

function get_bulk_actions() {
	$actions = array(
		'delete'=>'Delete'
		);

	return $actions;
	}


function column_cb($item) {
	return sprintf(
		'<input type="checkbox" name="client[]" value="%s"/>', $item['ID']
		);
	}

function prepare_items() {
	$columns = $this->get_columns();
	$hidden = array();
	$sortable = $this->get_sortable_columns();
	$this->_column_headers = array($columns, $hidden, $sortable);
	usort($this->client_data, array(&$this, 'usort_reorder'));

	$per_page = 5;
	$current_page = $this->get_pagenum();
	$total_items = count($this->client_data);

	$this->found_data = array_slice($this->client_data, (($current_page - 1) * $per_page), $per_page);

	$this->set_pagination_args(array(
		'total_items'=>$total_items,
		'per_page'=>$per_page
		));

	$this->items = $this->found_data;
	}

	}

function my_add_menu_items() {
	$hook = add_menu_page(
		'Client Location Editor Panel',
		'Client Location Editor',
		'activate_plugins',
		'my_client_list',
		'my_render_list_page'
		);

	add_action("load-$hook", 'add_options');
	}

function add_options() {
	global $clientListTable;
	
	$option = 'per_page';

	$args = array(
		'label'=>"Clients",
		'default'=>10,
		'option'=>'clients_per_page'
		);

	add_screen_option($option, $args);
	
	$clientListTable = new Service_Map_Table();
	}

add_action('admin_menu', 'my_add_menu_items');

function my_render_list_page() {
	global $clientListTable;
	
	$cli = $clientListTable;

	echo '</pre><div class="wrap"><h2>Location Editor</h2>';

	if (!isset($_GET['action']) && !isset($_POST['action']))
		echo '<div style="font-size:1.5em;font-weight:bold;">[&nbsp;<a href="?page='.$_GET['page'].'&action=add_client">Add New Client</a>&nbsp;]</div>';

	global $wpdb;
	
	if (!isset($_GET['action'])) {
		$fetch = $wpdb->get_results("select * from `wp_service_map_sites` order by `id` asc");

		$len = count($fetch);

		for($n=0;$n<$len;$n++) {
			$clientListTable->client_data[] = array(
				'ID'=>$fetch[$n]->id,
				'clientname'=>$fetch[$n]->label,
				'clientaddress'=>$fetch[$n]->street,
				'clientzip'=>$fetch[$n]->zip
				);
			}
		}

	$clientListTable->prepare_items();
	
	/* wp_service_map_sites */
	/* id, time, label, street, city, state, zip, lat, lng */

	$charset_collate = $wpdb->get_charset_collate();

	if (isset($_POST['action'])) {
		switch($_POST['action']) {
			case 'delete':
				if (isset($_POST['yesno']) && $_POST['yesno'] === 'yes') {
					if (!isset($_POST['client']))
						$clients = array();
						else
						$clients = $_POST['client'];

					$len = count($clients);
					
					if ($len < 1) {
						echo "<div style='font-size:1.5em;font-weight:bold;'>You did not select any clients to remove in bulk...</div>";
                                                echo "<div style='text-align:center;'><a href='?page=".$_GET['page']."'>Go Back</a></div>";

						} else {
						$query = '';

						for($n=0;$n<$len;$n++) {
							if ($query !== '')
								$query .= " or ";
							
							$query .= "`id`='".$clients[$n]."'";
							}

						$users = $wpdb->get_results("select * from `wp_service_map_sites` where ".$query);

						$l2 = count($users);
						for($n=0;$n<$l2;$n++) {
							$cli = $users[$n];

							echo "<div>&nbsp; (".($n+1).") Client: ".$cli->label." [".$cli->street." (".$cli->zip.")] REMOVED!</div>";
							}

						$wpdb->query("delete from `wp_service_map_sites` where ".$query);

						echo "<div style='margin-top:8px;text-align:center;'>";
						
						echo "<div style='text-align:center;'><a href='?page=".$_GET['page']."'>Go Back</a></div>";


						echo "</div>";
						}
					} else {
					$errored = false;
					
					if (!isset($_POST['client']))
						$clients = array();
						else
						$clients = $_POST['client'];

					$len = count($clients);

					if ($len < 1) {
						$errored = true;
						}

					if (!$errored) {
						echo "<form method='post' action='?page=".$_GET['page']."'>";

						$cond = '';

						for($n=0;$n<$len;$n++) {
							$client = $clients[$n];
							if ($cond !== '')
								$cond .= ' or ';

							$cond .= "`id`='".$client."'";
							}

						$query = 'select * from `wp_service_map_sites` where '.$cond;

						$results = $wpdb->get_results($query);
						
						$len = count($results);
						if ($len < 1) {
							echo "<div style='font-size:1.5em;font-weight:bold;'>You did not select any clients to remove in bulk...</div>";
	                                                echo "<div style='text-align:center;'><a href='?page=".$_GET['page']."'>Go Back</a></div>";
							} else {
							echo "<div style='margin-bottom:8px;font-size:1.5em;font-weight:bold;'>Are you sure you want to remove these clients?</div>";							

							for($n=0;$n<$len;$n++) {
								$cli = $results[$n];

								echo "<div style='font-weight:bold;margin-left:24px;'>";
								echo "<input type='hidden' name='client[]' value='".$cli->id."'>";

								echo "&nbsp; (".($n+1).") ".$cli->label." [".$cli->street." (".$cli->zip.")]";

								echo "</div>";
								}
							}
						
						echo "<div style='height:8px;'></div>";
						
						echo "<input type='hidden' name='action' value='delete'>";
	
						echo "<input type='hidden' name='yesno' value='yes'>";
						
						echo "<input type='submit' value='Yes, Remove ".($len)." Client".($len>1 ? 's' : '')."' style='width:45%;margin-right:5%;'>";

                                                echo "<input type='button' value='No, Go Back' style='width:45%;' onclick='window.location = \"?page=".$_GET['page']."\";'>";

						
						echo "</form>";
						} else {
						echo "<div style='font-size:1.5em;font-weight:bold;'>You did not select any clients to remove in bulk...</div>";
                                                echo "<div style='text-align:center;'><a href='?page=".$_GET['page']."'>Go Back</a></div>";
						}
					}
				break;
			}
		} elseif (isset($_GET['action'])) {
		/* Action HANDLER FUNTIONS */

		switch($_GET['action']) {
			case 'insert_client':
				$label = $_POST['label'];
				$street = $_POST['street'];
				$city = $_POST['city'];
				$state = $_POST['state'];
				$zip = $_POST['zip'];

				$errors = array();
				
				if (trim($label) === '') {
					$errors[] = 'Please enter a property / client name';
					}

				if (trim($street) === '') {
					$errors[] = 'Please enter a street address';
					}

				if (trim($city) === '') {
					$errors[] = 'Please enter a city name';
					}

				if (trim($state) === '') {
					$errors[] = 'Please enter a state';
					}
	
				if (!is_numeric($zip) || trim($zip.'') === '') {
					$errors[] = 'Please enter a valid zip code';
					}

				if (count($errors) < 1) {
					$exists = $wpdb->get_results(
						"select * from `wp_service_map_sites` where `label`='{$label}'"
						);
				
				

					if (count($exists) > 0) {
						$temp = $exists[0];
		
						if (strtolower($label) === $temp->label)
							$errors[] = 'The property / client name &quot;{$label}&quot; already exists';
						}
					}

				if (count($errors) > 0) {
					echo "<div style='font-weight:bold;font=size:1.5em;'><div>Error Adding Client:</div><div>".implode('</div><div>', $errors)."</div></div>";

					echo '<a href="?page='.$_GET['page'].'&action=add_client&label='.urlencode($label).'&street='.urlencode($street).'&city='.urlencode($city).'&state='.urlencode($state).'&zip='.urlencode($zip).'">Go Back</a>';
	
					} else {
					$geo = $cli->geocode("{$street}, {$city}, {$state}");
					
					if (!$geo) {
						$lat = null;
						$lng = null;
						} else {
						$lat = $geo[0];
						$lng = $geo[1];
						}

					$wpdb->insert('wp_service_map_sites', array(
						"label"=>$label,
						"street"=>$street,
						"city"=>$city,
						"state"=>$state,
						"zip"=>$zip,
						"time"=>time(),
						"lat"=>$lat,
						"lng"=>$lng
						));

					echo '<div style="font-size:2em;font-weight:bold;text-decoration:underline;margin-bottom:4px;">Client Added</div><div style="text-align:center;">';

					echo '<a href="?page='.$_GET['page'].'&action=add_client">Go Back</a>';

					echo '</div>';
					}
				break;
			
			case 'add_client':
				$label_val = isset($_GET['label']) ? str_replace('"', '', trim(stripslashes(stripslashes($_GET['label'])))) : '';
				if ($label_val !== '')
					$label_val = ' value="'.$label_val.'"';

				$street_val = isset($_GET['street']) ? str_replace('"', '', trim(stripslashes(stripslashes($_GET['street'])))) : '';
                                if ($street_val !== '')
                                        $street_val = ' value="'.$street_val.'"';

				$city_val = isset($_GET['city']) ? str_replace('"', '', trim(stripslashes(stripslashes($_GET['city'])))) : '';
                                if ($city_val !== '')
                                        $city_val = ' value="'.$city_val.'"';

				$state_val = isset($_GET['state']) ? str_replace('"', '', trim(stripslashes(stripslashes($_GET['state'])))) : '';
                                if ($state_val !== '')
                                        $state_val = ' value="'.$state_val.'"';
                                        
                                $zip_val = isset($_GET['zip']) ? str_replace('"', '', trim(stripslashes(stripslashes($_GET['zip'])))) : '';
                                if ($zip_val !== '')
                                        $zip_val = ' value="'.$zip_val.'"';

				echo '<form method="post" action="?page='.$_GET['page'].'&action=insert_client">';

				echo '<input autofocus="autofocus" name="label" type="text" placeholder="Property Name / Client" style="margin-bottom:2px;width:100%;"'.$label_val.'>';

				echo '<input name="street" type="text" placeholder="Street Address" style="margin-bottom:2px;width:100%;"'.$street_val.'>';

				echo '<input name="city" type="text" placeholder="City Name" style="margin-bottom:2px;width:100%;"'.$city_val.'>';

				echo '<input name="state" type="text" placeholder="State Name" style="margin-bottom:2px;width:100%;"'.$state_val.'>';

				echo '<input name="zip" type="text" placeholder="Zip Code" style="margin-bottom:2px;width:100%;"'.$zip_val.'>';
				
				echo '<div style="text-align:center;width:100%;margin-top:12px;">';

				echo '<input type="submit" value="Add New Client" style="width:45%;margin-right:5%;">';

				echo '<input type="button" onclick="window.location = \'?page='.$_GET['page'].'\';" value="Nevermind" style="width:45%;">';

				echo '</form>';
				break;

			case 'edit':
				if (count($_POST) > 0) {
					$label = $_POST['label'];
        	                        $street = $_POST['street'];
                	                $city = $_POST['city'];
                        	        $state = $_POST['state'];
                                	$zip = $_POST['zip'];

	                                $errors = array();
					
					

					if (count($errors) < 1) {

	                                if (trim($label) === '') {
                                        	$errors[] = 'Please enter a property / client name';
                                	        }

                        	        if (trim($street) === '') {
                	                        $errors[] = 'Please enter a street address';
        	                                }

	                                if (trim($city) === '') {
                                        	$errors[] = 'Please enter a city name';
                                	        }

                        	        if (trim($state) === '') {
                	                        $errors[] = 'Please enter a state';
        	                                }
	
                        	        if (!is_numeric($zip) || trim($zip.'') === '') {
                	                        $errors[] = 'Please enter a valid zip code';
        	                                }

					$current = $wpdb->get_results("select * from `wp_service_map_sites` where `id`='".$_GET['client']."'");

					if (!$current || count($current) < 1)
						$errors[] = 'You cannot edit a client that does not exist';

					$old_data = $current[0];
					
					$persist = 'client='.$_GET['client'].'&action='.$_GET['action'].'&label='.urlencode($label).'&street='.urlencode($street).'&city='.urlencode($city).'&state='.urlencode($state).'&zip='.urlencode($zip);

	                                if (count($errors) < 1) {
						if (!(is_numeric($old_data->lat) && is_numeric($old_data->lng)) || $street !== $old_data->street || $city !== $old_data->city || $state !== $old_data->state) {
							$geo = $cli->geocode("{$street}, {$city}, {$state}");

							if (!$geo) {
								$lat = NULL;
								$lng = NULL;
								} else {
								$lat = $geo[0];
								$lng = $geo[1];
								}
							} else {
							$lat = $old_data->lat;
							$lng = $old_data->lng;
							}

						$wpdb->update('wp_service_map_sites', array(
							'label'=>$label,
							'street'=>$street,
							'city'=>$city,
							'state'=>$state,
							'zip'=>$zip,
							'time'=>time(),
							'lat'=>$lat,
							'lng'=>$lng
							), array(
							'id'=>$_GET['client']
							)
						);

						echo "<div style='font-weight:bold;font-size:1.5em;'><div>Client Edited</div>";
						
						echo "<div style='margin-top:4px;'><a href=\"?page=".$_GET['page'].'&'.$persist."\">Go Back</a></div>";

						echo "<div><a href='?page=".$_GET['page']."'>Back to Client List</a></div>";
		
						echo "</div>";					
						}  else {
						echo "<div style='font-weight:bold;font=size:1.5em;'><div>Error Editing Client:</div><div>".implode('</div><div>', $errors)."</div></div>";

	                                        echo '<a href="?page='.$_GET['page'].'&'.$persist.'">Go Back</a>';

						}
						}
					} else {
					$id = $_GET['client'];

					$find = $wpdb->get_results("select * from `wp_service_map_sites` where `id`='{$id}'");

					if (count($find) < 1) {
						echo '<div>The client you are trying to edit does not exist</div>';
						echo '<div style="text-align:center;"><a href="?page='.$_GET['page'].'">Go Back</a></div>';
						} else {
					
						$find = $find[0];					
						$f = clone $find;
						$find = &$f;
						
						if (isset($_GET['label']))
							$find->label = $_GET['label'];

						if (isset($_GET['street']))
							$find->street = $_GET['street'];
	
						if (isset($_GET['city']))
							$find->city = $_GET['city'];
	
						if (isset($_GET['state']))
							$find->state = $_GET['state'];
	
						if (isset($_GET['zip']))
							$find->zip = $_GET['zip'];

						$label_val = ' value="'.str_replace('"', '', trim(stripslashes(stripslashes($find->label)))).'"';
						$street_val = ' value="'.str_replace('"', '', trim(stripslashes(stripslashes($find->street)))).'"';
                		                $city_val = ' value="'.str_replace('"', '', trim(stripslashes(stripslashes($find->city)))).'"';
        	                	        $state_val = ' value="'.str_replace('"', '', trim(stripslashes(stripslashes($find->state)))).'"';
	                                	$zip_val = ' value="'.str_replace('"', '', trim(stripslashes(stripslashes($find->zip)))).'"';
					

						echo '<form method="post" action="?page='.$_GET['page'].'&action=edit&client='.$_GET['client'].'">';

        	        	                echo '<input autofocus="autofocus" name="label" type="text" placeholder="New Property Name / Client" style="margin-bottom:2px;width:100%;"'.$label_val.'>';

        		                        echo '<input name="street" type="text" placeholder="New Street Address" style="margin-bottom:2px;width:100%;"'.$street_val.'>';

	                	                echo '<input name="city" type="text" placeholder="New City Name" style="margin-bottom:2px;width:100%;"'.$city_val.'>';

                                		echo '<input name="state" type="text" placeholder="New State Name" style="margin-bottom:2px;width:100%;"'.$state_val.'>';

                        	        	echo '<input name="zip" type="text" placeholder="New Zip Code" style="margin-bottom:2px;width:100%;"'.$zip_val.'>';
                                
	                	                echo '<div style="text-align:center;width:100%;margin-top:12px;">';

        		                        echo '<input type="submit" value="Edit Client" style="width:45%;margin-right:5%;">';

	        	                        echo '<input type="button" onclick="window.location = \'?page='.$_GET['page'].'\';" value="Nevermind" style="width:45%;">';

                        	        	echo '</form>';
						}
					}
				break;

			case 'delete':
				$find = $wpdb->get_results("select * from `wp_service_map_sites` where `id`='".$_GET['client']."'");

				if (count($find) < 1) {
					echo "<div style='font-weight:bold;font-size:1.5em;'>You cannot remove a client that does not exist.</div>";
					echo "<div><a href='?page={$_GET[page]}'>Go Back</a></div>";
					} else {
					if (count($_POST) > 0 && $_POST['yesno'] === 'yes') {
						$wpdb->delete('wp_service_map_sites', array('id'=>$_GET['client']));
						
						echo "<div style='font-size:1.5em;font-weight:bold;'>Client: ".$find[0]->label." [".$find[0]->street." (".$find[0]->zip.")] removed!</div>";

						echo "<div style='text-align:center;'><a href='?page=".$_GET['page']."'>Go Back</a></div>";
						} else {
						echo "<div style='text-align:center;'><form method='post' action='?page=".$_GET['page']."&action=delete&client=".$_GET['client']."'>";
						
						echo "<div>Are you sure you want to remove the client ".$find[0]->label." [".$find[0]->street." (".$find[0]->zip.")]?</div>";

						echo "<input type='hidden' name='yesno' value='yes'>";

						echo "<input type='submit' value='Yes, Remove Client' style='width:45%;margin-right:5%;'>";

						echo "<input type='button' value='No, Go Back' style='width:45%;' onclick='window.location = \"?page=".$_GET['page']."\";'>";
						
						echo '</div></form>';
						}
					}
				break;

			}
		} else {

		?>
		<form method="post">
			<input type="hidden" name="page" value="client_list_table">
			<?php
			$clientListTable->search_box('search', 'search_id');
	
		$clientListTable->display();
		echo '</form></div>';
		}
	}

/* EOF */
