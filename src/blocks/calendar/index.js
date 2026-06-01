import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
  edit( { attributes, setAttributes } ) {
    const { view, userId } = attributes;
    const blockProps = useBlockProps();
    return (
      <>
        <InspectorControls>
          <PanelBody title={ __( 'Calendar Settings', 'coreops-booking' ) }>
            <SelectControl
              label={ __( 'Default View', 'coreops-booking' ) }
              value={ view }
              options={ [
                { label: __( 'Month grid', 'coreops-booking' ),      value: 'dayGridMonth' },
                { label: __( 'Week (time grid)', 'coreops-booking' ), value: 'timeGridWeek' },
                { label: __( 'Agenda list', 'coreops-booking' ),      value: 'listWeek' },
              ] }
              onChange={ ( val ) => setAttributes( { view: val } ) }
            />
            <TextControl
              label={ __( 'Filter by User ID (optional)', 'coreops-booking' ) }
              value={ userId }
              onChange={ ( val ) => setAttributes( { userId: val } ) }
            />
          </PanelBody>
        </InspectorControls>
        <div { ...blockProps }>
          <p style={ { padding: '1rem', background: '#f0f0f1', borderRadius: '4px' } }>
            { __( '[ CoreOps Calendar ]', 'coreops-booking' ) }&nbsp;
            <small>{ view }</small>
          </p>
        </div>
      </>
    );
  },
  save() { return null; }, // server-side rendered
} );
