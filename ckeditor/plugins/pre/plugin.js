CKEDITOR.plugins.add( 'pre', {
  icons: 'code',
  init: function( editor ) {
    editor.addCommand( 'pre', {
      exec: function( editor ) {
        editor.insertHtml( '<pre>' + editor.getSelection().getSelectedText() + '</pre>' );
      }
    });
    editor.ui.addButton( 'Code', {
      label: 'Code',
      command: 'pre',
      toolbar: 'insert'
    });
  }
});