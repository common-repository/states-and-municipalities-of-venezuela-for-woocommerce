<?php

/**
 * States of Venezuela
 * - 1 Capital District
 * - 23 States
 * 
 * Sources:
 * - https://es.wikipedia.org/wiki/Anexo:Estados_federales_de_Venezuela
 * - https://es.wikipedia.org/wiki/Distrito_Capital_(Venezuela)
 */

function smvw_venezuelan_states($states) {
  $states['VE'] = array(
    'AM' => 'Amazonas',
    'AN' => 'Anzoátegui',
    'AP' => 'Apure',
    'AR' => 'Aragua',
    'BA' => 'Barinas',
    'BO' => 'Bolívar',
    'CA' => 'Carabobo',
    'CO' => 'Cojedes',
    'DE' => 'Delta Amacuro',
    'DC' => 'Distrito Capital',
    'FA' => 'Falcón',
    'GU' => 'Guárico',
    'LA' => 'Lara',
    'LG' => 'La Guaira',
    'ME' => 'Mérida',
    'MI' => 'Miranda',
    'MO' => 'Monagas',
    'NE' => 'Nueva Esparta',
    'PO' => 'Portuguesa',
    'SU' => 'Sucre',
    'TA' => 'Táchira',
    'TR' => 'Trujillo',
    'YA' => 'Yaracuy',
    'ZU' => 'Zulia',
  );

  // Use this filter to handle the States of Venezuela
  $states = apply_filters('smvw_custom_states_ve', $states);
  
  return $states;
}