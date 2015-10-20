<?php

add_action('admin_menu', 'kytracker_admin_menu');

function kytracker_admin_menu() {

	//Summary Page
	add_menu_page('Install Tracker', 'Install Tracker', 'administrator', 'kytracker_admin_page', 'kytracker_admin_page' , plugins_url('/images/icon.png', __FILE__) );
	//Products
	add_submenu_page( 'kytracker_admin_page', 'Products', 'Products', 'administrator', 'kytracker_admin_prods', 'kytracker_admin_prods' );
	add_submenu_page( 'kytracker_admin_prods', 'Delete', 'Delete', 'administrator', 'kytracker_admin_prods_del', 'kytracker_admin_prods_del' );
	//Registrations
	add_submenu_page( 'kytracker_admin_page', 'Registrations', 'Registrations', 'administrator', 'kytracker_admin_reg', 'kytracker_admin_reg' );
	add_submenu_page( 'kytracker_admin_reg', 'Edit', 'Edit', 'administrator', 'kytracker_admin_reg_view', 'kytracker_admin_reg_view' );
	//Upgrades
	add_submenu_page( 'kytracker_admin_page', 'Upgrades', 'Upgrades', 'administrator', 'kytracker_admin_upg', 'kytracker_admin_upg' );
	add_submenu_page( 'kytracker_admin_upg', 'Add', 'Add', 'administrator', 'kytracker_admin_upg_add', 'kytracker_admin_upg_add' );
	add_submenu_page( 'kytracker_admin_upg', 'Edit', 'Edit', 'administrator', 'kytracker_admin_upg_edit', 'kytracker_admin_upg_edit' );
	add_submenu_page( 'kytracker_admin_upg', 'Delete', 'Delete', 'administrator', 'kytracker_admin_upg_del', 'kytracker_admin_upg_del' );
}

function kytracker_admin_page() {
global $wpdb;
	echo '<div class="wrap">';
	echo '<h2>Install Tracker Summary</h2>';

	//We need some summary information for this page
	$table_name = $wpdb->prefix . "kyght_producttry";
	$prod_count = $wpdb->get_var( "SELECT count(*) FROM " . $table_name );
	$prod_usage = $wpdb->get_var( "SELECT sum(usecount) FROM " . $table_name );

	$table_name = $wpdb->prefix . "kyght_companytry";
	$comp_count = $wpdb->get_var( "SELECT count(*) FROM " . $table_name );
	$comp_avguse = $wpdb->get_var( "SELECT avg(usecount) FROM " . $table_name );
	$top10reg = $wpdb->get_results( "SELECT * FROM " . $table_name . " order by usecount" );
	
	echo '<div class="wrap">';
	?>
		<table class="widefat">
	    <thead>
	        <tr>
	            <th colspan="2" scope="col" class="manage-column column-name" style=""><h3>Product Summary</h3></th>
					</tr>
	    </thead>
	    <tbody>
				<tr>
					<td width="50%">Number of Products</td><td><?php echo $prod_count ?></td>
				</tr>
				<tr>
					<td>Total Usage</td><td><?php echo $prod_usage ?></td>
				</tr>
	    </tbody>
		</table>

		<br/>
		<table class="widefat">
	    <thead>
	        <tr>
	            <th colspan="2" scope="col" class="manage-column column-name" style=""><h3>Registration Summary</h3></th>
					</tr>
	    </thead>
	    <tbody>
	      <tr>
					<td width="50%">Number of Registrations</td><td><?php echo $comp_count ?></td>
				</tr>
				<tr>
					<td>Average Usage</td><td><?php echo $comp_avguse ?></td>
		    </tr>
				<tr>
					<td colspan="2"><strong>Top 10 by Usage</strong></td>
		    </tr>
	        <?php if( $top10reg ) { ?>

	            <?php
	            $count = 1;
	            $class = '';
	            foreach( $top10reg as $entry ) {
	                $class = ( $count % 2 == 0 ) ? ' class="alternate"' : '';
	            ?>

	            <tr<?php echo $class; ?>>
	                <td>
											<?php
												$path = 'admin.php?page=kytracker_admin_reg_view&id='.$entry->id;
												$url = admin_url($path);
												$link = "<a href='{$url}'>{$entry->name}</a>";
												echo $link;
												?>
									</td>
	                <td><?php echo $entry->email; ?></td>
	            </tr>

	            <?php
	                if ($count >= 10) break;
	                $count++;
	            }
	            ?>

	        <?php } else { ?>
	        <tr>
	            <td colspan="2">No Registrations received yet</td>
	        </tr>
	        <?php } ?>
	    </tbody>
		</table>

	
		<?
		
		
	echo '</div>';
	echo '</div>';
}

function kytracker_admin_prods() {
global $wpdb;
	echo '<div class="wrap">';
	echo '<h2>Products</h2>';

	//We need some summary information for this page
	$table_name = $wpdb->prefix . "kyght_producttry";
	$entries = $wpdb->get_results( "SELECT * FROM " . $table_name );
	echo '<div class="wrap">';

	?>
	<table class="widefat">
	    <thead>
	        <tr>
	            <th scope="col" class="manage-column column-name" style="">Product</th>
	            <th scope="col" class="manage-column column-name" style="">Version</th>
	            <th scope="col" class="manage-column column-name" style="">Usage</th>
	            <th scope="col" class="manage-column column-name" style="">Updated</th>
	            <th scope="col" style="text-align:right" width="10%"></th>
	        </tr>
	    </thead>

	    <tfoot>
	        <tr>
	            <th scope="col" class="manage-column column-name" style="">Product</th>
	            <th scope="col" class="manage-column column-name" style="">Version</th>
	            <th scope="col" class="manage-column column-name" style="">Usage</th>
	            <th scope="col" class="manage-column column-name" style="">Updated</th>
	            <th scope="col" style="text-align:right"  width="10%"></th>
	        </tr>
	    </tfoot>

	    <tbody>
	        <?php if( $entries ) { ?>

	            <?php
	            $count = 1;
	            $class = '';
	            foreach( $entries as $entry ) {
	                $class = ( $count % 2 == 0 ) ? ' class="alternate"' : '';
	            ?>

	            <tr<?php echo $class; ?>>
	                <td><?php echo $entry->product; ?></td>
	                <td><?php echo $entry->version; ?></td>
	                <td><?php echo $entry->usecount; ?></td>
	                <td><?php echo $entry->lastupdate; ?></td>
	                <td>
						      	<?php
										$path = 'admin.php?page=kytracker_admin_prods_del&id='.$entry->id;
										$url = admin_url($path);
										$link = "<a class='button-primary' href='{$url}'>Delete</a>";
										echo $link;
										?>
									</td>
	            </tr>

	            <?php
	                $count++;
	            }
	            ?>

	        <?php } else { ?>
	        <tr>
	            <td colspan="2">No Product usage received yet</td>
	        </tr>
	        <?php } ?>
	    </tbody>
	</table>

	<?
	echo '</div>';
	echo '</div>';
}


function kytracker_admin_reg() {
global $wpdb;

	echo '<div class="wrap">';
	echo '<h2>Registrations</h2>';

$table_name = $wpdb->prefix . "kyght_companytry";
$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
$limit = 5;
$offset = ( $pagenum - 1 ) * $limit;
$entries = $wpdb->get_results( "SELECT * FROM " . $table_name . " LIMIT $offset, $limit" );

echo '<div class="wrap">';

?>
<table class="widefat">
    <thead>
        <tr>
            <th scope="col" class="manage-column column-name" style="">Name</th>
            <th scope="col" class="manage-column column-name" style="">Email</th>
            <th scope="col" class="manage-column column-name" style="">Contact</th>
            <th scope="col" class="manage-column column-name" style="">Phone</th>
            <th scope="col" class="manage-column column-name" style="">City</th>
            <th scope="col" class="manage-column column-name" style="">Product</th>
            <th scope="col" class="manage-column column-name" style="">Version</th>
            <th scope="col" class="manage-column column-name" style="">Usage</th>
            <th scope="col" class="manage-column column-name" style="">Used</th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <th scope="col" class="manage-column column-name" style="">Name</th>
            <th scope="col" class="manage-column column-name" style="">Email</th>
            <th scope="col" class="manage-column column-name" style="">Contact</th>
            <th scope="col" class="manage-column column-name" style="">Phone</th>
            <th scope="col" class="manage-column column-name" style="">City</th>
            <th scope="col" class="manage-column column-name" style="">Product</th>
            <th scope="col" class="manage-column column-name" style="">Version</th>
            <th scope="col" class="manage-column column-name" style="">Usage</th>
            <th scope="col" class="manage-column column-name" style="">Used</th>
        </tr>
    </tfoot>

    <tbody>
        <?php if( $entries ) { ?>

            <?php
            $count = 1;
            $class = '';
            foreach( $entries as $entry ) {
                $class = ( $count % 2 == 0 ) ? ' class="alternate"' : '';
            ?>

            <tr<?php echo $class; ?>>
                <td>
										<?php
											$path = 'admin.php?page=kytracker_admin_reg_view&id='.$entry->id;
											$url = admin_url($path);
											$link = "<a href='{$url}'>{$entry->name}</a>";
											echo $link;
											?>
								</td>
                <td><?php echo $entry->email; ?></td>
                <td><?php echo $entry->contact; ?></td>
                <td><?php echo $entry->phone; ?></td>
                <td><?php echo $entry->city; ?></td>
                <td><?php echo $entry->product; ?></td>
                <td><?php echo $entry->version; ?></td>
                <td><?php echo $entry->usecount; ?></td>
                <td><?php echo $entry->lastused; ?></td>
            </tr>

            <?php
                $count++;
            }
            ?>

        <?php } else { ?>
        <tr>
            <td colspan="2">No Registrations received yet</td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?

$total = $wpdb->get_var( "SELECT COUNT(`id`) FROM " . $table_name );
$num_of_pages = ceil( $total / $limit );
$page_links = paginate_links( array(
    'base' => add_query_arg( 'pagenum', '%#%' ),
    'format' => '',
    'prev_text' => __( '&laquo;', 'aag' ),
    'next_text' => __( '&raquo;', 'aag' ),
    'total' => $num_of_pages,
    'current' => $pagenum
) );

if ( $page_links ) {
    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
}

echo '</div>';
echo '</div>';

}


function kytracker_admin_reg_view()
{
	global $wpdb;

	$id = $_GET['id'];

	if ($id == null) {
		echo "<h2>Unable to locate that upgrade record</h2>";
		exit;
	}

	$table_name = $wpdb->prefix . "kyght_companytry";
	$uprow = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE id = %d", $id) );

wp_enqueue_script('kytracker', plugins_url( '/js/trackerreg.js' , __FILE__ ) , array( 'jquery' ));
// including ajax script in the plugin Myajax.ajaxurl
wp_localize_script( 'kytracker', 'KyITrack', array( 'ajaxurl' => admin_url( 'admin-ajax.php') ) );
?>
   <div class="wrap">
      <h2>Edit Registration</h2>
      <form>
				<table class="widefat">
			    <tbody>
			      <tr><td>Name</td><td><input type="text" size="40" id="name" name="name" value="<?php echo $uprow->name ?>"/></td></tr>
			      <tr><td>Address</td><td><input type="text" size="40" id="address" name="address" value="<?php echo $uprow->address ?>"/></td></tr>
			      <tr><td>City</td><td><input type="text" size="40" id="city" name="city" value="<?php echo $uprow->city ?>"/></td></tr>
			      <tr><td>State</td><td><input type="text" id="state" size="40" name="state" value="<?php echo $uprow->state ?>"/></td></tr>
			      <tr><td>Zip\Postal</td><td><input type="text" id="zipcode" size="40" name="zipcode" value="<?php echo $uprow->zipcode ?>"/></td></tr>
			      <tr><td>Contact</td><td><input type="text" id="contact" size="40" name="contact" value="<?php echo $uprow->contact ?>"/></td></tr>
			      <tr><td>Email</td><td><input type="text" size="50" id="email" name="email" value="<?php echo $uprow->email ?>"/></td></tr>
			      <tr><td>Phone</td><td><input type="text" size="25" id="phone" name="phone" value="<?php echo $uprow->phone ?>"/></td></tr>
			      <tr><td>Product</td><td><input type="text" size="40" id="product" name="product" value="<?php echo $uprow->product ?>"/></td></tr>
			      <tr><td>Version</td><td><input type="text" size="20" id="version" name="version" value="<?php echo $uprow->version ?>"/></td></tr>
			      <tr><td>Custom</td><td><input type="text" size="20" id="custom" name="custom" value="<?php echo $uprow->custom ?>"/></td></tr>
			      <tr><td>Usage</td><td><?php echo $uprow->usecount ?></td></tr>
			      <tr><td>Last Used</td><td><?php echo $uprow->lastused ?></td></tr>
			      <tr>
						   <td><input type="button" id="submit" name="submit" value="Submit" class="button-primary"/></td>
						   <td style="text-align:right">
						      <?php
										$path = 'admin.php?page=kytracker_admin_reg_del&id='.$id;
										$url = admin_url($path);
										$link = "<a class='button-primary' href='{$url}'>Delete</a>";
										echo $link;
									?>
							 </td>
						</tr>
	    		<tbody>
         </table>
          <input type="hidden" id="id" name="id" value="<?php echo $id ?>"/>
          <input type="hidden" id="action" name="action" value="reg_edit"/>
      </form>
   </div>
<?php
}


function kytracker_admin_upg() {
global $wpdb;
	echo '<div class="wrap">';
	echo '<h2>Upgrades Available</h2>';

	//We need some summary information for this page
	$table_name = $wpdb->prefix . "kyght_upgrade";
	$entries = $wpdb->get_results( "SELECT * FROM " . $table_name );
	echo '<div class="wrap">';

	?>
	<table class="widefat">
	    <thead>
	        <tr>
			        <th scope="col" class="manage-column column-name" style="">Id</th>
	            <th scope="col" class="manage-column column-name" style="">Product</th>
	            <th scope="col" class="manage-column column-name" style="">Version</th>
	            <th scope="col" class="manage-column column-name" style="">Custom</th>
	            <th scope="col" class="manage-column column-name" style="">URL</th>
	            <th scope="col" class="manage-column column-name" style="">Notes</th>
	        </tr>
	    </thead>

	    <tfoot>
	        <tr>
			        <th scope="col" class="manage-column column-name" style="">Id</th>
	            <th scope="col" class="manage-column column-name" style="">Product</th>
	            <th scope="col" class="manage-column column-name" style="">Version</th>
	            <th scope="col" class="manage-column column-name" style="">Custom</th>
	            <th scope="col" class="manage-column column-name" style="">URL</th>
	            <th scope="col" class="manage-column column-name" style="">Notes</th>
	        </tr>
	    </tfoot>

	    <tbody>
	        <?php if( $entries ) { ?>

	            <?php
	            $count = 1;
	            $class = '';
	            foreach( $entries as $entry ) {
	                $class = ( $count % 2 == 0 ) ? ' class="alternate"' : '';
	            ?>

	            <tr<?php echo $class; ?>>
		            	<td>
										<?php
											$path = 'admin.php?page=kytracker_admin_upg_edit&id='.$entry->id;
											$url = admin_url($path);
											$link = "<a href='{$url}'>{$entry->id}</a>";
											echo $link;
											?>
									</td>
	                <td><?php echo $entry->product; ?></td>
	                <td><?php echo $entry->version; ?></td>
	                <td><?php echo $entry->custom; ?></td>
	                <td><?php echo $entry->url; ?></td>
	                <td><?php echo $entry->notesurl; ?></td>
	            </tr>

	            <?php
	                $count++;
	            }
	            ?>

	        <?php } else { ?>
	        <tr>
	            <td colspan="2">No Upgrades available yet</td>
	        </tr>
	        <?php } ?>
	    </tbody>
	</table>

	<?php
	echo '</div>';
	//Add Link to add Upgrade
	$path = 'admin.php?page=kytracker_admin_upg_add';
	$url = admin_url($path);
	$link = "<a href='{$url}'>Add</a>";
	echo $link;
	echo '</div>';
}


function kytracker_admin_upg_add()
{
wp_enqueue_script('kytracker', plugins_url( '/js/tracker.js' , __FILE__ ) , array( 'jquery' ));
// including ajax script in the plugin Myajax.ajaxurl
wp_localize_script( 'kytracker', 'KyITrack', array( 'ajaxurl' => admin_url( 'admin-ajax.php') ) );
?>
   <div class="wrap">
      <h2>Add New Upgrade</h2>
      <form>
				<table class="widefat">
			    <tbody>
			      <tr><td>Product</td><td><input size="40" type="text" id="product" name="product"/></td></tr>
			      <tr><td>Version</td><td><input size="15" type="text" id="version" name="version"/></td></tr>
			      <tr><td>Version Number</td><td><input size="15" type="text" id="vernum" name="vernum"/></td></tr>
			      <tr><td>Custom</td><td><input type="text" size="50" id="custom" name="custom"/></td></tr>
			      <tr><td>URL</td><td><input type="text" size="80" id="url" name="url"/></td></tr>
			      <tr><td>Notes (URL)</td><td><input type="text" size="80" id="notesurl" name="notesurl"/></td></tr>
	    		<tbody>
         </table>
          <input type="hidden" id="action" name="action" value="upgrade_add"/>
     			<input type="button" id="submit" name="submit" value="Submit" class="button-primary"/>
      </form>
   </div>
<?php
}

function kytracker_admin_upg_edit()
{
	global $wpdb;

	$id = $_GET['id'];

	if ($id == null) {
		echo "<h2>Unable to locate that upgrade record</h2>";
		exit;
	}
	
	$table_name = $wpdb->prefix . "kyght_upgrade";
	$uprow = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE id = %d", $id) );

wp_enqueue_script('kytracker', plugins_url( '/js/tracker.js' , __FILE__ ) , array( 'jquery' ));
// including ajax script in the plugin Myajax.ajaxurl
wp_localize_script( 'kytracker', 'KyITrack', array( 'ajaxurl' => admin_url( 'admin-ajax.php') ) );
?>
   <div class="wrap">
      <h2>Edit Upgrade</h2>
      <form>
				<table class="widefat">
			    <tbody>
			      <tr><td>Product</td><td><input type="text" size="40" id="product" name="product" value="<?php echo $uprow->product ?>"/></td></tr>
			      <tr><td>Version</td><td><input type="text" size="15" id="version" name="version" value="<?php echo $uprow->version ?>"/></td></tr>
			      <tr><td>Version Number</td><td><input type="text" size="15" id="vernum" name="vernum" value="<?php echo $uprow->vernum ?>"/></td></tr>
			      <tr><td>Custom</td><td><input type="text" id="custom" size="50" name="custom" value="<?php echo $uprow->custom ?>"/></td></tr>
			      <tr><td>URL</td><td><input type="text" id="url" size="80" name="url" value="<?php echo $uprow->url ?>"/></td></tr>
			      <tr><td>Notes (URL)</td><td><input type="text" size="80" id="notesurl" name="notesurl" value="<?php echo $uprow->notesurl ?>"/></td></tr>
			      <tr>
						   <td><input type="button" id="submit" name="submit" value="Submit" class="button-primary"/></td>
						   <td style="text-align:right">
						      <?php
									$path = 'admin.php?page=kytracker_admin_upg_del&id='.$id;
									$url = admin_url($path);
									$link = "<a class='button-primary' href='{$url}'>Delete</a>";
									echo $link;
									?>
							 </td>
						</tr>
	    		<tbody>
         </table>
          <input type="hidden" id="id" name="id" value="<?php echo $id ?>"/>
          <input type="hidden" id="action" name="action" value="upgrade_edit"/>
      </form>
   </div>
<?php
}

function kytracker_admin_upg_del()
{
	global $wpdb;

	$id = $_GET['id'];

	if ($id == null) {
		echo "<h2>Unable to locate that upgrade record</h2>";
		exit;
	}
	
	$table_name = $wpdb->prefix . "kyght_upgrade";
	$wpdb->delete( $table_name, array( 'id' => $id ) );
	
  echo "<h2>Upgrade Record Deleted</h2>";

}


function kytracker_admin_prods_del()
{
	global $wpdb;

	$id = $_GET['id'];

	if ($id == null) {
		echo "<h2>Unable to locate that product record</h2>";
		exit;
	}

	$table_name = $wpdb->prefix . "kyght_producttry";
	$wpdb->delete( $table_name, array( 'id' => $id ) );

  echo "<h2>Product Track Record Deleted</h2>";

}
