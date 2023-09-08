jQuery(document).ready(function($) {
    var summernoteContent = $('#summernote').summernote('code');
    $('#summernote_kent').summernote({
        height:350,
        minHeight:null,    // 定义编辑框最低的高度
        maxHeight:null,    // 定义编辑框最高的高度
        disableDragAndDrop:true,   // 禁止拖拽
        fontSizes: ['8', '9', '10', '11', '12', '14', '18', '24', '36', '48' , '64', '82', '150'],
        lang:'zh-TW',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear','fontsize']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['view', ['fullscreen', 'codeview']],
            ['help', ['help']]
        ],
    });
});