<div id="si_dashboard" class="wrap about-wrap">

	<h1><?php printf( __( 'Create Leads, <a href="%s">Sprout Clients</a>!' , 'sprout-invoices' ), self::PLUGIN_URL, self::SC_VERSION ); ?></h1>

	<div class="about-text"><?php _e( 'Thank you for using Sprout Clients at such an early stage of the development process &mdash; your feedback during this time is critical to it\'s success.' , 'sprout-invoices' ) ?></div>


	<h2 class="nav-tab-wrapper">
		<?php do_action( 'sprout_settings_tabs' ); ?>
	</h2>

	<div class="welcome_content clearfix">
		<div class="license-overview">
			<div class="activate_message clearfix">
				<div class="activation_msg clearfix">
					 <p>
						<h4><?php _e( 'First Things First...' , 'sprout-invoices' ) ?></h4>
						<?php _e( 'An active license for Sprout Clients provides support and updates. By activating your license, you can get automatic plugin updates from the WordPress dashboard. Updates provide you with the latest bug fixes and the new features each major release brings.' , 'sprout-invoices' ) ?></p>
				</div>
				<div class="activation_inputs clearfix">
					<input type="text" name="<?php echo SC_Updates::LICENSE_KEY_OPTION ?>" id="<?php echo SC_Updates::LICENSE_KEY_OPTION ?>" value="<?php echo SC_Updates::license_key() ?>" class="fat-input <?php echo 'license_'.SC_Updates::license_status() ?>" size="40" class="text-input">
					<?php if ( SC_Updates::license_status() != false && SC_Updates::license_status() == 'valid' ) : ?>
						<button id="sc_activate_license" class="button button-large" disabled="disabled"><?php _e( 'Activate License' , 'sprout-invoices' ) ?></button> 
						<button id="sc_deactivate_license" class="button button-large"><?php _e( 'Deactivate License' , 'sprout-invoices' ) ?></button>
					<?php else : ?>
						<button id="sc_activate_license" class="button button-primary button-large"><?php _e( 'Activate License' , 'sprout-invoices' ) ?></button>
					<?php endif ?>
					<div id="license_message" class="clearfix"></div>
				</div>
			</div>


			<h2 class="headline_callout"><?php _e( 'Welcome to Sprout Clients' , 'sprout-invoices' ) ?></h2>

			<div class="feature-section col three-col clearfix">
				<div class="col-1">
					<span class="flow_icon icon-notebook"></span>
					<h4><?php _e( 'Contact Management' , 'sprout-invoices' ); ?></h4>
					<p><?php _e( 'The premise of "managing" your contacts is to build relationships. Sprout Clients wants to make the process of building those relationships easier and less time consuming.', 'sprout-invoices' ); ?></p>
				</div>
				<div class="col-2">
					<span class="flow_icon icon-lightsaber"></span>
					<h4><?php _e( 'Jedi Automation Tricks' , 'sprout-invoices' ); ?></h4>
					<p><?php _e( 'Write to your leads/clients now and have them delivered later. Meet someone new and want to follow-up in a couple weeks, now there\'s no forgetting.' , 'sprout -invoices' ); ?></p>
				</div>
				<div class="col-3 last-feature">
					<span class="flow_icon icon-sproutapps-invoices"></span>
					<h4><?php _e( 'Engage with your Contacts', 'sprout -invoices' ); ?></h4>
					<p><?php _e( 'Sometimes you meet your clients or create specific engagements. Sprout Clients wants to tie those engagements to your clients for reference and automation.', 'sprout-invoices' ); ?></p>
				</div>
			</div>

		</div>
	</div>

	<hr />

	<div class="welcome_content">
		<h3><?php _e( 'FAQs' , 'sprout-invoices' ); ?></h3>

		<div class="feature-section col three-col clearfix">
			<div>
				<h4><?php _e( 'Where do I start?' , 'sprout-invoices' ); ?></h4>
				<p>
					<?php printf( __( "You can jump right in and start <a href='%s'>creating</a> your first lead but here are some important things to know first:" , 'sprout-invoices' ), admin_url( 'post-new.php?post_type=sa_client' ) ); ?>
				</p>
				<p>
					<ol>
						<li><?php _e( 'Each Lead should have a type set that signifies how it associates with you.' , 'sprout-invoices' ) ?></li>
						<li><?php _e( 'Even though most of our "leads" only have a single point of contact. Leads:Contacts is setup as one:many intentionally, trust us.' , 'sprout-invoices' ) ?></li>
						<li><?php _e( 'Statuses are set manually at the moment but in the future count on them being dynamically set by conditions. For now use them to organize your leads and better your workflows for follow-ups.' , 'sprout-invoices' ) ?></li>
						<li><?php _e( 'If you\'re a Sprout Invoices user than "Clients" are now "Leads".' , 'sprout-invoices' ) ?></li>

					</ol>
				</p>
			</div>
			<div>
				<h4><?php _e( 'Leads &amp; WordPress Users?' , 'sprout-invoices' ); ?></h4>
				<p><?php printf( __( '<a href="%s">Leads</a> have WordPress users associated with them and leads are not limited to a single user either. This allows for you to have multiple points of contact for a single lead. Leads can share contacts too...consider a use case where a "Lead" is a conference that you ended up meeting a lot of new contacts but you still have a "Lead" for each of those contacts.' , 'sprout-invoices' ), admin_url( 'edit.php?post_type=sa_client' ) ); ?></p>
			</div>
			<div class="last-feature">
				<h4><?php _e( 'Need help? Or an important feature?' , 'sprout-invoices' ); ?></h4>
				<p><?php printf( __( "We want to make sure using Sprout Invoices is enjoyable and not a hassle. Sprout Apps has some pretty awesome <a href='%s'>support</a> and a budding <a href='%s'>knowledgebase</a> that will help you get anything resolved." , 'sprout-invoices' ), self::PLUGIN_URL.'/support/', self::PLUGIN_URL.'/support/knowledgebase/' ); ?></p>

				<p><?php printf( "<a href='https://sproutinvoices.com/support/' target='_blank' class='button'>%s</a>", __( 'Support' , 'sprout-invoices' ) ); ?>&nbsp;<?php printf( "<a href='http://docs.sproutinvoices.com' target='_blank' class='button'>%s</a>", __( 'Documentation' , 'sprout-invoices' ) ); ?></p>

				<p><img class="footer_sa_logo" src="<?php echo SC_RESOURCES . 'admin/icons/sproutapps.png' ?>" /></p>

			</div>
		</div>

	</div>

</div>

