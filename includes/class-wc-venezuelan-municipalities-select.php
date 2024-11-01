<?php

/**
 * This class is a fork of "WC City Select" by 8manos
 * https://wordpress.org/plugins/wc-city-select/
 */

class WC_Venezuelan_Municipalities_Select {

  // plugin version
  const VERSION = '1.0.1';

  private $plugin_path;
  private $plugin_url;

  private $municipalities;

  public function __construct() {
    add_filter( 'woocommerce_billing_fields', array( $this, 'billing_fields' ), 10, 2 );
    add_filter( 'woocommerce_shipping_fields', array( $this, 'shipping_fields' ), 10, 2 );
    add_filter( 'woocommerce_form_field_city', array( $this, 'form_field_city' ), 10, 4 );

    //js scripts
    add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
  }

  public function billing_fields( $fields, $country ) {
    $fields['billing_city']['type'] = 'city';

    return $fields;
  }

  public function shipping_fields( $fields, $country ) {
    $fields['shipping_city']['type'] = 'city';

    return $fields;
  }

  public function get_cities( $cc = null ) {
    if ( empty( $this->municipalities ) ) {
      $this->load_country_cities();
    }

    if ( ! is_null( $cc ) ) {
      return isset( $this->municipalities[ $cc ] ) ? $this->municipalities[ $cc ] : false;
    } else {
      return $this->municipalities;
    }
  }

  public function load_country_cities() {
    global $municipalities;

    // Load only the city files the shop owner wants/needs.
    $allowed = array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );

    if ( $allowed ) {
      foreach ( $allowed as $code => $country ) {
        if ( ! isset( $municipalities[ $code ] ) && file_exists( $this->get_plugin_path() . 'municipalities/' . $code . '.php' ) ) {
          include( $this->get_plugin_path() . 'municipalities/' . $code . '.php' );
        }
      }
    }

    $this->municipalities = apply_filters( 'wc_city_select_cities', $municipalities );
  }

  public function form_field_city( $field, $key, $args, $value ) {

    // Do we need a clear div?
    if ( ( ! empty( $args['clear'] ) ) ) {
      $after = '<div class="clear"></div>';
    } else {
      $after = '';
    }

    // Required markup
    if ( $args['required'] ) {
      $args['class'][] = 'validate-required';
      $required = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce'  ) . '">*</abbr>';
    } else {
      $required = '';
    }

    // Custom attribute handling
    $custom_attributes = array();

    if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
      foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
        $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
      }
    }

    // Validate classes
    if ( ! empty( $args['validate'] ) ) {
      foreach( $args['validate'] as $validate ) {
        $args['class'][] = 'validate-' . $validate;
      }
    }

    // field p and label
    $field  = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $args['id'] ) . '_field">';
    if ( $args['label'] ) {
      $field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) .'">' . $args['label']. $required . '</label>';
    }

    // Get Country
    $country_key = $key == 'billing_city' ? 'billing_country' : 'shipping_country';
    $current_cc  = WC()->checkout->get_value( $country_key );

    $state_key = $key == 'billing_city' ? 'billing_state' : 'shipping_state';
    $current_sc  = WC()->checkout->get_value( $state_key );

    // Get country cities
    $municipalities = $this->get_cities( $current_cc );

    if ( is_array( $municipalities ) ) {

      $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="city_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '">
        <option value="">'. esc_attr__( 'Select a municipality&hellip;', 'states-and-municipalities-of-venezuela-for-woocommerce' ) .'</option>';

      if ( $current_sc && $municipalities[ $current_sc ] ) {
        $dropdown_cities = $municipalities[ $current_sc ];
      } else if ( is_array( reset($municipalities) ) ) {
        $dropdown_cities = array_reduce( $municipalities, 'array_merge', array() );
        sort( $dropdown_cities );
      } else {
        $dropdown_cities = $municipalities;
      }

      foreach ( $dropdown_cities as $city_name ) {
        $field .= '<option value="' . esc_attr( $city_name ) . '" '.selected( $value, $city_name, false ) . '>' . $city_name .'</option>';
      }

      $field .= '</select>';

    } else {

      $field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
    }

    // field description and close wrapper
    if ( $args['description'] ) {
      $field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
    }

    $field .= '</p>' . $after;

    return $field;
  }

  public function load_scripts() {
    if ( is_cart() || is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) {

      $city_select_path = $this->get_plugin_url() . 'assets/js/municipality-select.js';
      wp_enqueue_script( 'wc-city-select', $city_select_path, array( 'jquery', 'woocommerce' ), self::VERSION, true );

      $municipalities = json_encode( $this->get_cities() );
      wp_localize_script( 'wc-city-select', 'wc_city_select_params', array(
        'cities' => $municipalities,
        'i18n_select_city_text' => esc_attr__( 'Select a municipality&hellip;', 'states-and-municipalities-of-venezuela-for-woocommerce' )
      ) );
    }
  }

  public function get_plugin_path() {

    if ( $this->plugin_path ) {
      return $this->plugin_path;
    }

    return $this->plugin_path = SMVW_PLUGIN_PATH;
  }

  public function get_plugin_url() {

    if ( $this->plugin_url ) {
      return $this->plugin_url;
    }

    return $this->plugin_url = SMVW_PLUGIN_URL;
  }
}