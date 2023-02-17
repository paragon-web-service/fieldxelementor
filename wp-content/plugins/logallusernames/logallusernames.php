<?php
/*
Plugin Name: Subscriber Table
Description: Displays a table of all subscribers' usernames, email addresses, and registration dates
Version: 1.2
Author: Sean Baker
*/
// Display a table of all subscribers' usernames, email addresses, and registration dates
function subscriber_table() {
    $subscribers = get_users(array('role' => 'subscriber'));
    $table = '<table>';
    $table .= '<tr>';
    $table .= '<th>Username</th>';
    $table .= '<th>Email</th>';
    $table .= '<th>Registration Date</th>';
    $table .= '</tr>';
    foreach ($subscribers as $subscriber) {
        $table .= '<tr>';
        $table .= '<td>' . $subscriber->user_login . '</td>';
        $table .= '<td>' . $subscriber->user_email . '</td>';
        $table .= '<td>' . $subscriber->user_registered . '</td>';
        $table .= '</tr>';
    }
    $table .= '</table>';
    return $table;
}
// Add the shortcode [subscriber_table]
add_shortcode('subscriber_table', 'subscriber_table');