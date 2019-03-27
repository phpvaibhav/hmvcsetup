<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo SITE_TITLE ?> | Password Reset</title>
    
</head>
<style>
.button {
      background-color: #00a358;
      border-top: 10px solid #3869D4;
      border-right: 18px solid #3869D4;
      border-bottom: 10px solid #3869D4;
      border-left: 18px solid #3869D4;
      display: inline-block;
      color: #FFF;
      text-decoration: none;
      border-radius: 3px;
      box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
      -webkit-text-size-adjust: none;
    }
    
    .button--green {
      background-color: #ff7701;
      border-top: 10px solid #ff7701;
      border-right: 18px solid #ff7701;
      border-bottom: 10px solid #ff7701;
      border-left: 18px solid #ff7701;
    }
</style>
 <?php
        $backend_assets =  base_url().'backend_asset/';
    ?>
<body style="font-family: 'Source Sans Pro', sans-serif; padding:0; margin:0;">
    <table style="max-width: 750px; margin: 0px auto; width: 100% ! important; background: #F3F3F3; padding:30px 30px 30px 30px;" width="100% !important" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td style="text-align: center; background: ##fff;">
                <table width="100%" border="0" cellpadding="30" cellspacing="0">    
                    <tr>
                        <td>
                          <center>
                            <img style="max-width: 125px; width: 100%;padding: 10px;" src="<?php echo $backend_assets ?>images/logo.png">
                          </center>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <tr>
            <td style="text-align: center;">
                <table width="100%" border="0" cellpadding="30" cellspacing="0" bgcolor="#fff">
                    <tr>
                        <td>

                            <h3 style="color: #333; font-size: 28px; font-weight: normal; margin: 0; text-transform: capitalize;">Reset Password</h3>
                            <p style="text-align: left; color: #333; font-size: 16px; line-height: 28px;">Hello <?php echo $name ?>,</p>
                            <p style="text-align: left;color: #333; font-size: 16px; line-height: 28px;">You recently requested to reset your password for your Alka Silver Lake account. Please click on button To reset your password: </p>
                            <table class="body-action" align="center" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                          <td align="center">
                            <!-- Border based button
                       https://litmus.com/blog/a-guide-to-bulletproof-buttons-in-email-design -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                              <tr>
                                <td align="center">
                                  <table border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td>
                                        <a style="border: 10px #419ec7 solid;background:#419ec7;color: white;text-decoration: none;" class="button button--green" href="<?php echo $link ?>">Reset Password</a>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                            
                            <p style="text-align: left;color: #333; font-size: 16px; line-height: 28px;">If you didn't generate this link, don't worry. You can login with your old password. This link is only for one time use.</p>  

                            <p style="text-align: left;color: #333; font-size: 16px; line-height: 28px;">Thanks,<br><?php echo SITE_TITLE ?> team</p>  
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#fff">
                    <tr>
                        <td style="padding: 10px;background: #419ec7;color: #fff;"><?php echo COPYRIGHT; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>