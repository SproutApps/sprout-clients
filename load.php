<?php

/**
 * Load the SC application
 *
 * @package Sprout_Clients
 * @return void
 */
function sprout_clients_load() {

	if ( class_exists( 'Sprout_Client' ) ) {
		sc_deactivate_plugin();
		return; // already loaded, or a name collision
	}

	do_action( 'sprout_clients_preload' );

	//////////
	// Load //
	//////////

	/**
	 * Master class
	 */
	require_once SC_PATH.'/Sprout_Clients.php';

	// models
	require_once SC_PATH.'/models/_Model.php';
	require_once SC_PATH.'/models/Client.php';
	if ( file_exists( SC_PATH.'/models/Engagement.php' ) ) {
		require_once SC_PATH.'/models/Engagement.php';
		require_once SC_PATH.'/models/Message.php';
	}
	require_once SC_PATH.'/models/Record.php';

	// controllers
	require_once SC_PATH.'/controllers/_Controller.php';
	require_once SC_PATH.'/languages/Sprout_Clients_l10n.php';
	if ( ! class_exists( 'Sprout_Invoices' ) ) {
		require_once SC_PATH.'/controllers/admin/_Settings.php';
	}
	require_once SC_PATH.'/controllers/admin/Settings.php';
	require_once SC_PATH.'/controllers/admin/Capabilities.php';
	// controllers -- clients
	require_once SC_PATH.'/controllers/clients/Clients.php';
	require_once SC_PATH.'/controllers/clients/Clients_Users.php';
	require_once SC_PATH.'/controllers/clients/Clients_Admin_Meta_Boxes.php';
	require_once SC_PATH.'/controllers/clients/Clients_Admin_Table.php';
	require_once SC_PATH.'/controllers/clients/Clients_AJAX.php';
	require_once SC_PATH.'/controllers/clients/Clients_Tax.php';
	// controllers -- history
	require_once SC_PATH.'/controllers/history/Client_History.php';
	if ( ! SC_FREE_TEST && file_exists( SC_PATH.'/controllers/history/Engagement_History.php' ) ) {
		// controllers -- engagments
		require_once SC_PATH.'/controllers/history/Engagement_History.php';
	}
	require_once SC_PATH.'/controllers/history/SI_History.php';
	if ( ! SC_FREE_TEST && file_exists( SC_PATH.'/controllers/engagements/Engagements.php' ) ) {
		// controllers -- engagments
		require_once SC_PATH.'/controllers/engagements/Engagements.php';
		require_once SC_PATH.'/controllers/engagements/Engagements_Edit.php';
		require_once SC_PATH.'/controllers/engagements/Engagements_Admin_Table.php';
		require_once SC_PATH.'/controllers/engagements/Engagements_AJAX.php';
		require_once SC_PATH.'/controllers/engagements/Engagements_Client.php';
		require_once SC_PATH.'/controllers/engagements/Engagements_Tax.php';
	}
	if ( ! SC_FREE_TEST && file_exists( SC_PATH.'/controllers/messages/Messages.php' ) ) {
		// controllers -- messages
		require_once SC_PATH.'/controllers/messages/Messages.php';
		require_once SC_PATH.'/controllers/messages/Messages_Admin.php';
		require_once SC_PATH.'/controllers/messages/Messages_Template.php';
		require_once SC_PATH.'/controllers/messages/Messages_Route.php';
	}

	require_once SC_PATH.'/controllers/records/Internal_Records.php';
	require_once SC_PATH.'/controllers/records/Records_Admin_Table.php';

	// updater if SI isn't loaded
	if ( ! class_exists( 'EDD_SL_Plugin_Updater_SA_Mod' ) && file_exists( SC_PATH.'/controllers/updates/Updater.php' ) ) {
		require_once SC_PATH.'/controllers/updates/Updater.php';
	}

	// updates
	if ( file_exists( SC_PATH.'/controllers/updates/Updates.php' ) ) {
		require_once SC_PATH.'/controllers/updates/Updates.php';
	}

	if ( file_exists( SC_PATH.'/controllers/updates/Free_License.php' ) ) {
		require_once SC_PATH.'/controllers/updates/Free_License.php';
	}

	// template-tags
	require_once SC_PATH.'/template-tags/sprout-clients.php';
	require_once SC_PATH.'/template-tags/sprout-engagements.php';
	require_once SC_PATH.'/template-tags/forms.php';
	require_once SC_PATH.'/template-tags/ui.php';
	require_once SC_PATH.'/template-tags/users.php';
	require_once SC_PATH.'/template-tags/utility.php';

	require_once SC_PATH.'/controllers/compat/Compatibility.php';

	/**
	 * Master Model
	 */
	SC_Post_Type::init();

	/**
	 * Client Model
	 */
	Sprout_Client::init();

	if ( ! SC_FREE_TEST && class_exists( 'Sprout_Engagement' ) ) {
		/**
		 * Engagment Model
		 */
		Sprout_Engagement::init();
	}

	if ( ! SC_FREE_TEST && class_exists( 'SC_Message' ) ) {
		/**
		 * Messages Model
		 */
		SC_Message::init();
	}

	/**
	 * Shared Models
	 */
	SC_Record::init();

	/**
	 * Master Controller
	 */
	SC_Controller::init();

	/**
	 * Records is shared with Sprout Invoices
	 */
	SC_Internal_Records::init();

	/**
	 * l10n
	 */
	SC_l10n::init();

	/**
	 * Settings
	 */
	if ( ! class_exists( 'Sprout_Invoices' ) ) {
		SA_Settings_API::init();
	}

	SC_Settings::init();

		// updates
	if ( ! SC_FREE_TEST && class_exists( 'SC_Updates' ) ) {
		SC_Updates::init();
	}
	if ( class_exists( 'SC_Free_License' ) ) {
		SC_Free_License::init();
	}

	/**
	 * Clients
	 */
	SC_Clients::init();
	SC_Users::init();
	SC_Clients_Admin_Meta_Boxes::init();
	SC_Clients_Admin_Table::init();
	SC_Clients_AJAX::init();
	Sprout_Clients_Tax::init();

	if ( ! SC_FREE_TEST && class_exists( 'Sprout_Engagements' ) ) {
		/**
		 * Engagements
		 */
		Sprout_Engagements::init();
		Sprout_Engagements_Edit::init();
		Sprout_Engagements_Admin_Table::init();
		Sprout_Engagements_AJAX::init();
		Sprout_Engagements_Client::init();
		Sprout_Engagements_Tax::init();
	}

	SC_Client_History::init();
	if ( ! SC_FREE_TEST && class_exists( 'SC_Engagement_History' ) ) {
		SC_Engagement_History::init();
	}
	SC_Invoices_History::init();

	if ( ! SC_FREE_TEST && class_exists( 'Sprout_Clients_Messages' ) ) {
		/**
		 * Messaging
		 */
		Sprout_Clients_Messages::init();
		Sprout_Clients_Messages_Admin::init();
		Sprout_Clients_Messages_Template::init();
		Sprout_Clients_Messages_Route::init();
	}

	SC_Admin_Capabilities::init();
	SC_Compatibility::init();

	do_action( 'sprout_clients_loaded' );

}
