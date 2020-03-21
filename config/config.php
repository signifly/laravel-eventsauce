<?php

return [

    /*
     * The name of the table that stores the domain messages.
     */
    'domain_messages_table' => 'domain_messages',

    /*
     * Specify the database connection for the message repository.
     *
     * It defaults to your default database connection.
     */
    'message_database_connection' => null,

    /*
     * The name of the table that stores the state messages.
     */
    'state_messages_table' => 'domain_states',

    /*
     * Specify the database connection for the state repository.
     *
     * It defaults to your default database connection.
     */
    'state_database_connection' => null,

    /*
     * The fully qualified class names of your custom decorators.
     *
     * They will be resolved using the service container.
     */
    'custom_decorators' => [],

];
