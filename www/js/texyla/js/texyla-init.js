$.texyla.setDefaults({
    baseDir: '{{$baseUri}}js/texyla',
    previewPath: '{{$previewPath}}',
    filesPath: '{{$filesPath}}',
    filesUploadPath: '{{$filesUploadPath}}',
    filesMkDirPath: '{{$filesMkDirPath}}',
    filesRenamePath: '{{$filesRenamePath}}',
    filesDeletePath: '{{$filesDeletePath}}'
});

$(function () {
    $("textarea.texyla").livequery(function(){
        $(this).texyla({
            toolbar: [
            'h2', 'h3', 'h4',
            null,
            'bold', 'italic',
            null,			
            'ul', 'ol',
            null,
            'link', 'img', 'table',
            null,
            'color',
            null,
            /*'files',
			null,*/

            ],
            texyCfg: $(this).data('texy-cfg'),
            bottomLeftToolbar: ['edit', 'preview'],
            bottomRightPreviewToolbar : [],
            buttonType: "span",
            tabs: false
        });
    });
        
    $.texyla({
        buttonType: "button"
    });
});