<?php
	function witalk_get_woo_number() {
	// If get_plugins() isn't available, require it
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
	// Create the plugins folder and file variables
	$plugin_folder = get_plugins( '/' . 'woocommerce' );
	$plugin_file = 'woocommerce.php';
	
	// If the plugin version number is set, return it 
	if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
		return $plugin_folder[$plugin_file]['Version'];

	} else {
	// Otherwise return null
		return NULL;
	}
}
	
		
		//Termékek lekérdezése
function witalk_get_all_product() {
	$full_product_list = array();
	$args = array(
			'post_type' => 'product',
			'fields'         => 'ids',
			'post_status' => 'publish',
			'posts_per_page' => '-1'
		);
		//Woocommerce verzio
		$version =witalk_get_woo_number();
$products = new WP_Query($args);
			foreach ($products->posts as $postitem) {
$theid =$postitem;
if ($version <3.0) {
$product = get_product( $theid);
} else {
$product = wc_get_product($theid);
		}
if( ($product->is_type( 'simple' ) || $product->is_type( 'variable' )) && (get_post_meta($theid,'italkereso_hide',true) != 'yes') ) {
if($product->is_type( 'simple' ) &&$product->is_in_stock()) {
	$thetitle = get_the_title($theid);
	$excerpt =str_replace( '"', '""', get_the_excerpt($theid) );
if (empty ($excerpt)) {
$excerpt =str_replace( '"', '""', get_the_content($theid) );
}
$shipping_cost =get_post_meta( $theid, 'shipping_cost', true );
$shipping_time =get_post_meta( $theid, 'shipping_time', true );
if ($version <3.0) {
$sku =$product->sku; //Cikkszám
if ($product->get_sale_price() >0)  {
			   $price =$product->get_sale_price(); //Ha létezik akciós ár
			   				   } else {
				   				   $price =$product->get_price_including_tax();//Ha van áfa, az is kell
				   }
			//$net_price =round($product->get_price_excluding_tax());
} else {
	$sku =$product->get_sku(); //Cikkszám
				   				   $price =wc_get_price_including_tax($product);//Ha van áfa, az is kell
			//$net_price =round(wc_get_price_excluding_tax($product));
}
						$url  =get_permalink( $theid);
			$category ="";
			   			   $terms =get_the_terms($theid, 'product_cat');
			   if (count($terms) >0) {
				foreach ($terms as $term) {
			   $category.=", ".$term->name;
				   }
			   }
				   $category =trim($category, ", ");
				   				   					$_temp = wp_get_attachment_image_src( get_post_thumbnail_id( $theid), 'full' ); //Fotólink
																			if (!empty( $_temp) ) {
					   $picture_link =wp_get_attachment_url( get_post_thumbnail_id($theid) );
						} else {
						$picture_link ="";
						}
						$full_product_list[] = array("title"=>$thetitle, "id"=>$theid, "category" =>$category, "price"=>$price, "url" =>$url, "kep" =>$picture_link, "excerpt" =>$excerpt, "shipping_cost" =>$shipping_cost, "shipping_time" =>$shipping_time);
}
if($product->is_type( 'variable' ) &&$product->is_in_stock() ) {
						foreach($product->get_available_variations() as $variation) {
							$variation_data = new WC_Product_Variation($variation['variation_id']);
							if (get_post_meta($variation['variation_id'],'italkereso_hide',true) != 'yes') {
							$thetitle = get_post_meta( $variation['variation_id'], 'italk_prod_name', true );
if (empty($thetitle) ) {
$thetitle =get_the_title($theid);
}
$excerpt =str_replace( '"', '""', get_the_excerpt($theid) );
if (empty ($excerpt)) {
$excerpt =str_replace( '"', '""', get_the_content($theid) );
}
$shipping_cost =get_post_meta( $theid, 'shipping_cost', true );
$shipping_time =get_post_meta( $theid, 'shipping_time', true );
if ($version <3.0) {
if ($variation_data->get_sale_price() >0)  {
			   $price =$variation_data->get_sale_price(); //Ha létezik akciós ár
			   				   } else {
				   $price =$variation_data->get_regular_price();
				   }
				   } else {
				   $price =wc_get_price_including_tax($variation_data);//Ha van áfa, az is kell
				   }
						$url  =get_permalink( $theid);
			$category ="";
			   			   $terms =get_the_terms($theid, 'product_cat');
			   if (count($terms) >1) {
				foreach ($terms as $term) {
			   $category.=", ".$term->name;
				   }
			   }
				   $category =trim($category, ", ");
				   				   					$_temp = wp_get_attachment_image_src( get_post_thumbnail_id( $theid), 'full' ); //Fotólink
						if (!empty( $_temp )) {
					   $picture_link =wp_get_attachment_url( get_post_thumbnail_id($theid) );
						} else {
						$picture_link ="";
						}
$full_product_list[] = array("title"=>$thetitle, "id"=>$theid, "category" =>$category,  "price"=>$price, "url" =>$url, "kep" =>$picture_link, "excerpt" =>$excerpt, "shipping_cost" =>$shipping_cost, "shipping_time" =>$shipping_time);
}
						}
}
}
    //endwhile;
	}
wp_reset_query();
return $full_product_list;
}
//Feed elkészítése
	function witalk_customRSS(){
	if (!is_feed("italkereso")){//csak egyszer kell lefutnia
		add_feed('italkereso', 'witalk_italkeresoXML');
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
	}
	function witalk_italkeresoXML() {
		header("Content-type: text/xml");
echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<products>";
$products =witalk_get_all_product();
if (count($products) >0) {
foreach ($products as $product) {
echo "<product>";
echo "<identifier>".$product['id']."</identifier>";
echo "<name><![CDATA[".strip_tags($product['title'])."]]></name>";
	echo "<category>".strip_tags($product['category'])."</category>";
echo "<price>".$product['price']."</price>";
echo "<product_url>".$product['url']."</product_url>";
if (!empty($product['kep'])) {
echo "<image_url>".$product['kep']."</image_url>";
}
echo "<description><![CDATA[".strip_tags($product['excerpt'])."]]></description>";
$shipping_cost =get_option('witalk_shipping_cost');
$shipping_time =get_option('witalk_shipping_time');
if (!empty($product['shipping_cost'])) {
echo "<delivery_cost>".$product['shipping_cost']."</delivery_cost>";
} 
elseif (!empty($shipping_cost)){
echo "<delivery_cost>".get_option('witalk_shipping_cost')."</delivery_cost>";
}
if (!empty($product['shipping_time'])) {
echo "<delivery_time>".$product['shipping_time']."</delivery_time>";
} 
elseif (!empty($shipping_time)){
echo "<delivery_time>".get_option('witalk_shipping_time')."</delivery_time>";
}

	echo "</product>";
}
} else {
echo "Hiba: Nincs elérhető termék!";
}
echo "</products>";
	}
	function witalk_add_menu_page () {
	add_options_page("Italkereső beállításai", "Italkereső", "manage_options", "italkereso", "witalk_settings");
		}
		function witalk_settings () {
		?>
		<div class ="wrap">
			<p><img src ="<?php echo home_url();?>/wp-content/plugins/woo-italkereso/assets/image/logo-160.jpg" width ="120px"></p>
				
		<h2>Italkereső beállításai</h2>
		
		<p>Ahhoz, hogy megjelenjen az Italkereső.hu rendszerében először regisztrálnia kell cégét oldalunkon.
<br>A regisztrációval kapcsolatos bővebb információt és feltételeket az <a target ="_blank" href ="https://italkereso.hu/bemutatkozas">alábbi címen érhet el</a>
<p>Sikeres regisztráció után, belépve az Italkereső.hu rendszerbe, a beállítások menüpont „Automatikus termék frissítés URL” résznél kell megadni az alábbi URL-t:
		<?php
		echo get_feed_link("italkereso");
				?>
		<form method="post" action="admin-post.php">
		<input type="hidden" name="action" value="save_witalk_options" />
<?php wp_nonce_field( 'Witalk' ); ?>

		<table class="form-table">
		<tr valign="top">
		<th scope="row"><label for ="witalk_shipping_cost">Alapértelmezett szállítási költség</label></th>
		<td><input type="text" name="witalk_shipping_cost" id ="witalk_shipping_cost" value="<?php echo esc_attr( get_option('witalk_shipping_cost') );?>" /></td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for ="witalk_shipping_time">Alapértelmezett szállítási idő (napokban megadva)</label></label></th>
		<td><input type="text" name="witalk_shipping_time" id ="witalk_shipping_time" value="<?php echo esc_attr( get_option('witalk_shipping_time') );?>" /></td>
	</tr>
	</table>
	<?php submit_button();?>
	</form>
	</div>
	<?php
			}
						add_action( 'admin_init', 'witalk_admin_init');
function witalk_admin_init() {
	add_action( 'admin_post_save_witalk_options', 'process_witalk_options' );
}
function process_witalk_options (){
	if ( !current_user_can( 'manage_options' ) ) {
wp_die( 'Not allowed' );
	}
check_admin_referer( 'Witalk' );
	update_option("witalk_shipping_cost", sanitize_text_field($_POST["witalk_shipping_cost"]));
update_option("witalk_shipping_time", sanitize_text_field($_POST["witalk_shipping_time"]));
		wp_redirect( add_query_arg( 'page', 'italkereso', admin_url( 'options-general.php' ) ) );
}
			//mezők hozzáadása a termék adatlaphoz
			add_action( 'woocommerce_product_options_general_product_data', 'witalk_add_extra_fields' );
			
			function witalk_add_extra_fields() {
				woocommerce_wp_checkbox( array( 'id' => 'italkereso_hide', 'label' => 'A termék elrejtése az Italkereső árlistából', 'description' => 'Ha be van pipálva, ez a termék nem lesz bent az Italkereső kínálatában.' ) );
woocommerce_wp_text_input(
        array(
            'id' => 'shipping_time',
            'label' =>'Termék kiszállítási ideje',
            'placeholder' => 'Termék kiszállításának ideje',
            'desc_tip' => 'true',
            'description' => 'A termékhez tartozókiszállítási idő napban'
        )
    );
	woocommerce_wp_text_input(
        array(
            'id' => 'shipping_cost',
            'label' =>'Termék szállítási költsége',
            'placeholder' => 'Termék szállítási költsége',
            'desc_tip' => 'true',
            'description' => 'A termékhez tartozó szálítási költség'
        )
    );
				}
				function witalk_save_fields($post_id) {
				$italk_hide =$_POST["italkereso_hide"];
												update_post_meta( $post_id, 'italkereso_hide', esc_attr( $italk_hide ) );
																						update_post_meta( $post_id, 'shipping_cost', esc_attr( $_POST["shipping_cost"] ) );
											update_post_meta( $post_id, 'shipping_time', esc_attr( $_POST["shipping_time"] ) );
																}
					add_action( 'woocommerce_process_product_meta', 'witalk_save_fields' );
					add_action( 'woocommerce_product_after_variable_attributes', 'witalk_add_extra_variation_fields', 10, 3 );
					function witalk_add_extra_variation_fields ($loop, $variation_data, $variation) {
		echo '<div class="variation-custom-fields">';
		woocommerce_wp_checkbox( 
		array(
		'id' => 'italkereso_hide['. $loop .']',
		'label' => 'A termék elrejtése az Italkereső xml-ből',
		'description' => 'Ha be van pipálva, ez a termék nem lesz bent az Italkereső xml-ben.',
		'value'         => get_post_meta($variation->ID, 'italkereso_hide', true) 
		) );
						woocommerce_wp_text_input(
        array(
            'id' => 'italk_prod_name['. $loop .']',
            'label' =>'Megjelenő terméknév az Italkeresőben',
            'placeholder' => 'Érdemes az adott variációra szabni',
            'desc_tip' => 'true',
            'description' => 'Az adott termék egyedi neve',
			'value'         => get_post_meta($variation->ID, 'italk_prod_name', true) 
        )
    );
				
						}
						add_action( 'woocommerce_save_product_variation', 'italk_save_variation_fields', 10, 2 );
					function italk_save_variation_fields ($variation_id, $i) {
		$italk_hide =$_POST["italkereso_hide"][$i];
												update_post_meta( $variation_id, 'italkereso_hide', esc_attr( $italk_hide ) );
												update_post_meta( $variation_id, 'italk_prod_name', esc_attr( $_POST["italk_prod_name"][$i] ) );
																																								}
?>