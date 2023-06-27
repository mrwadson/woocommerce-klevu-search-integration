jQuery(document).ready(
    function ($) {
        const $ecCheckbox = $('#woocommerce_klevu_track_search_integration_enabled');

        updateToggles();

        $ecCheckbox.change( updateToggles );

        function updateToggles() {
            const isEnabled = $ecCheckbox.is( ':checked' );
            toggleCheckboxRow( $( '.klevu-settings' ), isEnabled );
        }

        function toggleCheckboxRow( checkbox, isVisible ) {
            if ( isVisible ) {
                checkbox.closest( 'tr' ).show();
            } else {
                checkbox.closest( 'tr' ).hide();
            }
        }
    }
);
