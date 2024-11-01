( function ( blocks, editor, components , i18n, element ) {

  const el   = element.createElement;  
  const { InspectorControls } = editor;
  const {SelectControl } = components;
  var registerBlockType = blocks.registerBlockType;
  var __ = i18n.__;
  

  registerBlockType( 'super/related-post-block', {
      title: __( 'Related Posts (SUPER)', 'super-related-post' ),
      icon: 'list-view',
      description: __( 'Description of the super related block', 'super-related-post' ),
      keywords: ['super', 'Related posts', 'related-post'],
      category: 'common',
      attributes: {
        related_post: {
              type: 'string'
          },
      },
      edit: function (props) {
        var attributes = props.attributes; 

        var related_post_details = el('fieldset',{
            className:'super-related-posts-list-fieldset'},
                el(SelectControl,{
                  value : attributes.related_post,
                  label: __('Select Related post display option', 'super_related-post-data-for-wp'),
                  options:[
                    { label: 'Select option', value: '' },
                    { label: 'Related Post1', value: 'related_post_1' },
                    { label: 'Related Post2', value: 'related_post_2' },
                    { label: 'Related Post3', value: 'related_post_3' },
                  ] ,
                  onChange: function(value){
                        props.setAttributes( { related_post: value } ); 
                  }
              }),
              
          );

        return [el('div',
                    {className:'super-related-post-block-container'},
                    related_post_details
                )
                ];
      },
      save: function (props) {
          return null;   
      },
  });
} )(
window.wp.blocks,
window.wp.editor,
window.wp.components,
window.wp.i18n,
window.wp.element,
);