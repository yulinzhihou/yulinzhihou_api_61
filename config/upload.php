<?php
// +----------------------------------------------------------------------
// | GS Admin 后台上传设置设置
// +----------------------------------------------------------------------

return [
    // 最大上传
    'maxsize'  => '20mb',
    // 文件保存格式化方法
    'savename' => '/storage/{topic}/{year}{mon}{day}/{filesha1}{.suffix}',
    // 文件格式限制
    'mimetype' => 'jpg,png,bmp,jpeg,gif,webp,zip,rar,xls,xlsx,doc,docx,wav,mp4,mp3,pdf,txt',
    // 需要转换成 webp 格式的图片
    'is_to_webp' => ['jpg','png','bmp','jpeg','gif'],
    // 图片转换成webp后是否删除源文件，默认删除，可以减少空间占用
    'to_webp_delete_origin' => true,
    // 是否将上传文件上传到云存储，前提是需要开启云存储，对接。默认使用服务器本地文件上传到云存储。
    'is_upload_to_cloud' => false,
    // 上传云端后，本地文件是否删除，默认不删除。
    'is_upload_to_cloud_delete_native' => false,
];