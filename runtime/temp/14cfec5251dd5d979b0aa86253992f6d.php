<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:82:"/www/wwwroot/aldedar.shalage.top/public/../application/admin/view/member/edit.html";i:1616136868;s:75:"/www/wwwroot/aldedar.shalage.top/application/admin/view/layout/default.html";i:1611580233;s:72:"/www/wwwroot/aldedar.shalage.top/application/admin/view/common/meta.html";i:1611580233;s:74:"/www/wwwroot/aldedar.shalage.top/application/admin/view/common/script.html";i:1611580233;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<meta name="referrer" content="never">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<?php if(\think\Config::get('fastadmin.adminskin')): ?>
<link href="/assets/css/skins/<?php echo \think\Config::get('fastadmin.adminskin'); ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
<?php endif; ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>

    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav') && \think\Config::get('fastadmin.breadcrumb')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <?php if($auth->check('dashboard')): ?>
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                    <?php endif; ?>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <form id="edit-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Openid'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-openid" data-rule="required" class="form-control" name="row[openid]" type="text" value="<?php echo htmlentities($row['openid']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Nickname'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-nickname" data-rule="required" class="form-control" name="row[nickname]" type="text" value="<?php echo htmlentities($row['nickname']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Gender'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
                        
            <select  id="c-gender" data-rule="required" class="form-control selectpicker" name="row[gender]">
                <?php if(is_array($genderList) || $genderList instanceof \think\Collection || $genderList instanceof \think\Paginator): if( count($genderList)==0 ) : echo "" ;else: foreach($genderList as $key=>$vo): ?>
                    <option value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['gender'])?$row['gender']:explode(',',$row['gender']))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Phone'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-phone" class="form-control" name="row[phone]" type="text" value="<?php echo htmlentities($row['phone']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Avatar'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-avatar" data-rule="required" class="form-control" size="50" name="row[avatar]" type="text" value="<?php echo htmlentities($row['avatar']); ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-avatar" class="btn btn-danger faupload" data-input-id="c-avatar" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp" data-multiple="false" data-preview-id="p-avatar"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-avatar" class="btn btn-primary fachoose" data-input-id="c-avatar" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right" for="c-avatar"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-avatar"></ul>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Country'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-country" class="form-control" name="row[country]" type="text" value="<?php echo htmlentities($row['country']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Province'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-province" class="form-control" name="row[province]" type="text" value="<?php echo htmlentities($row['province']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Citys'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-citys" class="form-control" name="row[citys]" type="text" value="<?php echo htmlentities($row['citys']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Money'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-money" data-rule="required" class="form-control" step="0.01" name="row[money]" type="number" value="<?php echo htmlentities($row['money']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Status'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            
            <div class="radio">
            <?php if(is_array($statusList) || $statusList instanceof \think\Collection || $statusList instanceof \think\Paginator): if( count($statusList)==0 ) : echo "" ;else: foreach($statusList as $key=>$vo): ?>
            <label for="row[status]-<?php echo $key; ?>"><input id="row[status]-<?php echo $key; ?>" name="row[status]" type="radio" value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['status'])?$row['status']:explode(',',$row['status']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label> 
            <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Prevtime'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-prevtime" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[prevtime]" type="text" value="<?php echo $row['prevtime']?datetime($row['prevtime']):''; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Logintime'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-logintime" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[logintime]" type="text" value="<?php echo $row['logintime']?datetime($row['logintime']):''; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Jointime'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-jointime" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[jointime]" type="text" value="<?php echo $row['jointime']?datetime($row['jointime']):''; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Topid'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-topid" data-rule="required" class="form-control" name="row[topid]" type="number" value="<?php echo htmlentities($row['topid']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Is_zx'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
                        
            <select  id="c-is_zx" data-rule="required" class="form-control selectpicker" name="row[is_zx]">
                <?php if(is_array($isZxList) || $isZxList instanceof \think\Collection || $isZxList instanceof \think\Paginator): if( count($isZxList)==0 ) : echo "" ;else: foreach($isZxList as $key=>$vo): ?>
                    <option value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['is_zx'])?$row['is_zx']:explode(',',$row['is_zx']))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
