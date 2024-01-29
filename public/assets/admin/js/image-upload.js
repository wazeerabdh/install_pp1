function readURL(input) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();

        reader.onload = function (e) {
            $('#viewer').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}

$("#customFileEg1").change(function () {
    readURL(this);
});

$('.delete_file_input').click(function () {
    let $parentDiv = $(this).closest('div');
    $parentDiv.find('input[type="file"]').val('');
    $parentDiv.find('.img_area_with_preview img').attr("src", " ");
    $(this).hide();
});

$('.custom-upload-input-file').on('change', function(){
    if (parseFloat($(this).prop('files').length) !== 0) {
        let $parentDiv = $(this).closest('div');
        $parentDiv.find('.delete_file_input').fadeIn();
    }
    let $parentDiv = $(this).closest('div');
    uploadColorImage($parentDiv, $(this));
})


function uploadColorImage($parentDiv, thisData) {
    if (thisData && thisData[0].files.length > 0) {
        $parentDiv.find('.img_area_with_preview img').attr("src", window.URL.createObjectURL(thisData[0].files[0]));
        $parentDiv.find('.img_area_with_preview img').removeClass('d-none');
        $parentDiv.find('.existing-image-div img').addClass('d-none');
        $parentDiv.find('.delete_file_input').fadeIn();
    }
}
