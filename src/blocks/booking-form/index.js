import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
  edit( { attributes, setAttributes } ) {
    const { userId } = attributes;
    const blockProps = useBlockProps();
    return (
      <>
        <InspectorControls>
          <PanelBody title={ __( 'Booking Form Settings', 'coreops-booking' ) }>
            <TextControl
              label={ __( 'Pre-fill User ID (optional)', 'coreops-booking' ) }
              help={ __( 'Associates submitted bookings with a specific CoreOps user.', 'coreops-booking' ) }
              value={ userId }
              onChange={ ( val ) => setAttributes( { userId: val } ) }
            />
          </PanelBody>
        </InspectorControls>
        <div { ...blockProps }>
          <p style={ { padding: '1rem', background: '#f0f0f1', borderRadius: '4px' } }>
            { __( '[ CoreOps Booking Form ]', 'coreops-booking' ) }
          </p>
        </div>
      </>
    );
  },
  save() { return null; },
} );
