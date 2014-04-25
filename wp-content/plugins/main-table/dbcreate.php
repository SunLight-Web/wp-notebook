  <?php
global $wpdb;


// EMAIL HISTORY

    if($wpdb->get_var("show tables like sent_email_history") != 'sent_email_history') 
  {
    $sql = "CREATE TABLE sent_email_history (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    email_subject text COLLATE utf8_general_ci NOT NULL,
    email_text text COLLATE utf8_general_ci NOT NULL,
    email_recivers text COLLATE utf8_general_ci NOT NULL,
    email_mailheaders text COLLATE utf8_general_ci NOT NULL,
    email_sent_date date NOT NULL,
    UNIQUE KEY id (id)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

// TABLE_OF_CLIENTS

  if($wpdb->get_var("show tables like table_of_clients") != 'table_of_clients') 
  {
    $sql = "CREATE TABLE table_of_clients (
    id                       mediumint(9) NOT NULL AUTO_INCREMENT,
    client_name              text COLLATE utf8_general_ci NOT NULL,
    client_typeOfDeal        text COLLATE utf8_general_ci NOT NULL,
    client_contacts          text COLLATE utf8_general_ci NOT NULL,
    client_email             text COLLATE utf8_general_ci NOT NULL,
    client_country           text COLLATE utf8_general_ci NOT NULL,
    client_source            text COLLATE utf8_general_ci NOT NULL,
    client_date              date NOT NULL,
    client_date_lm           date NOT NULL,
    client_whoisworking      text COLLATE utf8_general_ci NOT NULL,
    client_MEMO              text COLLATE utf8_general_ci NOT NULL,
    client_objects           text COLLATE utf8_general_ci NOT NULL,
    client_uid               mediumint(9) NOT NULL,
    client_priority          boolean NOT NULL,
    client_poll_hstry        text COLLATE utf8_general_ci NOT NULL,
    client_messaging_hstry text COLLATE utf8_general_ci NOT NULL,
    UNIQUE KEY id (id)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }



// NEW_VALUES

  if($wpdb->get_var("show tables like new_values") != 'new_values') 
  {

    $sql = "CREATE TABLE new_values (
    id                       mediumint(9) NOT NULL AUTO_INCREMENT,
    deal                     text COLLATE utf8_general_ci NOT NULL,
    source                   text COLLATE utf8_general_ci NOT NULL,
    country                  text COLLATE utf8_general_ci NOT NULL,
    worker                   text COLLATE utf8_general_ci NOT NULL,
    UNIQUE KEY id (id)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }


// POLL_DATA 

  if($wpdb->get_var("show tables like poll_data") != 'poll_data') 
  {

    $sql = "CREATE TABLE poll_data (
    id                       mediumint(9) NOT NULL AUTO_INCREMENT,
    poll_link                     text COLLATE utf8_general_ci NOT NULL,
    csv_link                      text COLLATE utf8_general_ci NOT NULL,
    poll_name                     text COLLATE utf8_general_ci NOT NULL,
    poll_category                 text COLLATE utf8_general_ci NOT NULL,
    UNIQUE KEY id (id)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

// CONFIG_TZK
  if($wpdb->get_var("show tables like config_tzk") != 'config_tzk') 
  {

    $sql = "CREATE TABLE config_tzk (
    id                       mediumint(9) NOT NULL AUTO_INCREMENT,
    objects_link                  text COLLATE utf8_general_ci NOT NULL,
    UNIQUE KEY id (id)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
  ?>












