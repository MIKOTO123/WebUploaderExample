const $ = require('jquery');
const WebUploader = require('webuploader');


//注册方法
WebUploader.Uploader.register({
    "before-send-file": "beforeSendFile", "before-send": "beforeSend", "after-send-file": "afterSendFile"
}, {
    //注册方法
    beforeSendFile: function (file) {
        console.log('before-send-file');//每个文件发送前调用一次
        var deferred = WebUploader.Deferred();

        deferred.resolve();
        //此处计算md5?


        // setTimeout(() => {
        //     deferred.resolve();
        //
        // }, 5000);


        return deferred.promise();


    }, beforeSend: function (block) {
        console.log('before-send');//每个分片都会调用
        var deferred = WebUploader.Deferred()
        let _this = this;
        console.log(this);
        console.log(block);
        console.log(block.file);
        console.log(block.file.cusmd5val);

        block.file.chunkSize

        //获取options,判断checkChunkmd5参数,决定是否计算分片md5
        if (this.options.checkChunkmd5) {
            WebUploader.md5File(block.file, block.chunk * block.chunkSize, block.end).then(function (val) {
                console.log("我计算陈工");
                deferred.resolve();
                // $.ajax({
                //     url: "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", type: "POST", data: {
                //         md5: val
                //     }, success: function (response) {
                //         if (response.success) {
                //             deferred.reject();
                //             // uploader.skipFile(block.file);
                //         } else {
                //             deferred.resolve();
                //         }
                //     }
                // });
            });
        } else {
            $.ajax({
                url: "http://www.wue.com/background/checkChunkUploaded.php",
                type: "POST",
                data: {
                    cusmd5val: block.file.cusmd5val,
                    chunk: block.chunk,
                    chunks: block.chunks,
                    name: block.file.name,
                },
                success: function (response) {
                    if (response.success) {
                        deferred.reject();
                        // uploader.skipFile(block.file);
                    } else {
                        deferred.resolve();
                    }
                }
            });

        }


        //
        // setTimeout(() => {
        //     // deferred.resolve();
        //     if (block.chunk == 1) {
        //         deferred.resolve();
        //     }
        //     deferred.reject();
        //     console.log("我好了" + block.chunk);
        // }, 5000);

        return deferred.promise();
        // console.log(block);
    }, afterSendFile: function (file) {
        console.log('after-send-file');//每个文件完成后调用一次
        //这边通知文件进行合成,并检测合成状态


        var deferred = WebUploader.Deferred();
        deferred.resolve();
        return deferred.promise();
    }
});


var uploader = WebUploader.create({
    //去掉自动上传,防止md5没计算出来就跑到后面去了
    // auto: true,
    pick: {
        id: '#uploader',
        label: '点击选择文件'
    },
    server: "http://www.wue.com/background/upload.php",
    fileVal: 'Filedata',
    duplicate: true,//是否重复上传
    compress: false,//不压缩图片
    chunked: true,
    chunkSize: 5242880,//超过5m就分片
    resize: false,
    checkChunkmd5: false,//自定义参数,是否开启分片md5检测,(默认只检测分片是否存在和分片大小即可,可节省资源和时间)


    // resume: true,
    // 'before-send-file': function (file) {
    //     // 获取文件的MD5值，用于在服务器端判断文件是否已存在或已上传部分分片
    //     uploader.md5File(file, 0, file.size).then(function (md5) {
    //         uploader.options.formData.chunkMd5 = md5;
    //     });
    // },
    // 'chunksend': function (file, chunk) {
    //     // 每个分片上传前，可能还需要携带分片索引和总分片数等信息
    //     console.log('chunkend');
    //     uploader.options.formData.chunkIndex = chunk.index;
    //     uploader.options.formData.chunkCount = chunk.chunks.length;
    // },
});


//文件上传前,这个方法是异步的,执行结果并不会影响后续上传
//obj参数代表当前文件对象，data参数代表表formData对象，headers参数代表请求头对象
//此处建议组装上传参数data
uploader.on('uploadBeforeSend', function (obj, data, headers) {

    console.log('uploadBeforeSend');
    // console.log(data);
    console.log(obj);
    //actual
    data.uid = obj.file.source.uid;//此处uid,随机生成,及时是同一个文件,每次也会不一样,但是这一次,分片的uid是一样的,
    data.cusmd5val = obj.file.cusmd5val;//文件md5
    data.chunkactualsize = obj.file.end - obj.file.start;//分片文件大小

    //此处执行异步方法,不会影响文件上传结果,所以并没有效果
    var deferred = WebUploader.Deferred()
    setTimeout(() => {
        deferred.resolve();
        console.log("是否有效果?");//就是咩有效果,并不会阻塞
    }, 5000);
    return deferred.promise();


});


uploader.on('uploadStart', function (file) {
    console.log('uploadStart');//在
    // console.log('guid:' + WebUploader.Base.guid());//每次都是随机生成
    //看看你什么后调用
    //做一次ajax用来统计

});

//
//文件上传成功
uploader.on('uploadSuccess', function (file, response) {
    console.log('uploadSuccess');
    //多片文件只调用一次


    console.log(file);
    console.log(response);
});
//uploadAccept
uploader.on('uploadAccept', function (file, response) {

    console.log('uploadAccept');
    //判断response的内容是否为"上传失败",
    //如果是,则抛出错误,并阻止文件上传
    //如果不是,则允许文件上传
    console.log(file);
    console.log(response);
    console.log(response._raw);
    //如果status等于1,代表失败
    var response_raw = JSON.parse(response._raw);
    if (response_raw.status === 1) {
        console.log("上传失败");
        return false;
    }


});


uploader.on('uploadError', function (file, reason) {
    console.log('uploadError');
    //抛出500 http响应码 以上 异常后会触发
    console.log(file);
    console.log(reason);
    alert("上传失败");

});
//
// uploader.on('uploadComplete', function (file) {
//     console.log('uploadComplete');
//     console.log(file);
// });
//
// uploader.on('uploadProgress', function (file, percentage) {
//     console.log('uploadProgress');
//     console.log(file, percentage);
// });
//
// uploader.on('uploadFinished', function () {
//     console.log('uploadFinished');
//     console.log('finished');
// });
//
// uploader.on('error', function (err) {
//     console.log('error');
//     console.log(err);
// });
//
// uploader.on('all', function (type) {
//     console.log('all');
//     console.log(type);
//     if (type === 'startUpload') {
//         console.log('start upload');
//     }
// });
//
// uploader.on('beforeFileQueued', function (file) {
//     console.log('beforeFileQueued');
//     console.log(file);
// });
//

//文件被加入队列
uploader.on('fileQueued', function (file) {
    //官方在例子这里计算md5值
    console.log('fileQueued');
    // uploader.md5File(file).then(function (val) {
    //     file.cusmd5val = val;
    // });
    // console.log(file);
});
//
// //文件被移除队列
// uploader.on('fileDequeued', function (file) {
//     console.log('fileDequeued');
//     console.log(file);
// });
//
// //
uploader.on('filesQueued', function (files) {
    console.log('filesQueued');

    $.each(files, function (i, file) {
        //计算文件md5值,并放入file对象中.文件很大的话,是否考虑只取前面一部分进行md5计算?
        uploader.md5File(file).then(function (val) {
            file.cusmd5val = val;
            uploader.upload(file);
        });

    });

    // console.log(file);
});


let myPromise = new Promise((resolve, reject) => {
    setTimeout(() => {
        if (true) {
            resolve('成功的结果');
        } else {
            reject(new Error('异步操作失败'));
        }
    }, 1000);
});

myPromise.then(result => {
    console.log('成功:', result);
}).catch(error => {
    console.error('失败:', error.message);
});


console.log(123);