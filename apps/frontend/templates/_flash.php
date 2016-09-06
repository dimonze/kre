<object id="myId" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="<?= $height ?>">
  <param name="movie" value="/swf/<?= $file ?>" />
  <param name="wmode" value="transparent" />
    <!--[if !IE]>-->
    <object type="application/x-shockwave-flash" data="/swf/<?= $file ?>" wmode="transparent" width="100%" height="<?= $height ?>">
    <!--<![endif]-->
      <div> </div>
    <!--[if !IE]>-->
    </object>
    <!--<![endif]-->
</object>