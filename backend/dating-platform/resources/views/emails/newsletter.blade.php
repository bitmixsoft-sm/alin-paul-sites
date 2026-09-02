<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  @php
    $token = env('AUTOREGISTER_TOKEN', '98eb7a12f25a609674cfe50a064a84c2');
  @endphp
  <!--[if !mso]><!-->
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!--<![endif]-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title></title>
  <style type="text/css">
.ReadMsgBody { width: 100%; background-color: #ffffff; }
.ExternalClass { width: 100%; background-color: #ffffff; }
.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div { line-height: 100%; }
html { width: 100%; }
body { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; margin: 0; padding: 0; }
table { border-spacing: 0; table-layout: fixed; margin: 0 auto; }
table table table { table-layout: auto; }
.yshortcuts a { border-bottom: none !important; }
img:hover { opacity: 0.9 !important; }
a { color: #ff5e3a; text-decoration: none; }
.textbutton a { font-family: 'open sans', arial, sans-serif !important;}
.btn-link a { color:#FFFFFF !important;}

/*Responsive*/
@media only screen and (max-width: 640px) {
body { margin: 0px; width: auto !important; font-family: 'Open Sans', Arial, Sans-serif !important;}
.table-inner { width: 90% !important;  max-width: 90%!important;}
.table-full { width: 100%!important; max-width: 100%!important;}
}

@media only screen and (max-width: 479px) {
body { width: auto !important; font-family: 'Open Sans', Arial, Sans-serif !important;}
.table-inner{ width: 90% !important;}
.table-full { width: 100%!important; max-width: 100%!important;}
/*gmail*/
u + .body .full { width:100% !important; width:100vw !important;}
}
</style>
</head>

<body class="body">
  <table class="full" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center">
        <table align="center" width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td align="center" bgcolor="#414a51" style="background-size:auto; background-repeat:repeat-x; background-position:top;">
              <table align="center" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="600" align="center">
                    <table class="table-inner" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="40"></td>
                      </tr>
                      <tr>
                        <td align="center">
                          <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tr>
                              <td width="30" valign="bottom">
                                <table width="100%" border="0" align="right" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td height="35"></td>
                                  </tr>
                                  <tr>
                                    <td height="25" bgcolor="#FFFFFF" style="border-top-left-radius:6px;font-size:0px;">&nbsp;</td>
                                  </tr>
                                </table>
                              </td>
                              <!-- headline -->
                              <td align="center" valign="bottom" background="{{URL::to('/')}}/images/title-bg.png" style="background-image: url({{URL::to('/')}}/images/title-bg.png); background-repeat: repeat-x; background-size: auto; background-position: bottom;">
                                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td bgcolor="#ff5e3a" style="border-radius:6px;" align="center">
                                      <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td height="15"></td>
                                        </tr>
                                        <tr>
                                          <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:16px; font-weight: bold;">{{$data['name']}} {{l("wants to see you!", $data['lang'])}}</td>
                                        </tr>
                                        <tr>
                                          <td height="15"></td>
                                        </tr>
                                      </table>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                              <!-- end headline -->
                              <td width="30" valign="bottom">
                                <table width="100%" border="0" align="left" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td height="35"></td>
                                  </tr>
                                  <tr>
                                    <td height="25" bgcolor="#FFFFFF" style="border-top-right-radius:6px;font-size:0px;">&nbsp;</td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                    <table class="table-inner" width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td bgcolor="#FFFFFF" align="center">
                          <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td height="40"></td>
                            </tr>
                            <!--logo-->
                            <tr>
                              <td align="center">
                                <a href="#" style="display: block; background: #ff5e3a; margin: 0 auto; width: 60px; height: 60px; border-radius:50%">
                                  <img style="display: block; line-height: 0px; font-size: 0px; margin: 0px auto; border: 0px; padding-top: 8px; margin-left: 10px;" src="{{URL::to('/')}}/img/logo.png" alt="img" />
                                </a>
                              </td>
                            </tr>
                            <!--end logo-->
                            <tr>
                              <td height="5"></td>
                            </tr>
                            <!--slogan-->
                            <tr>
                              <td align="center" style="font-family: 'Open Sans', Arial, sans-serif; color:#95a5a6; font-size:12px; letter-spacing: 1px;line-height: 28px; font-style:italic;">{{config('app.name')}}</td>
                            </tr>
                            <!--end slogan-->
                            <tr>
                              <td height="40"></td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                    <table class="table-inner" width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td bgcolor="#FFFFFF" align="center">
                          <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                            <!--img-->
                            <tr>
                              <td align="center">
                                <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td align="center" style="line-height: 0px;">
                                      <img style="display:block; line-height:0px; font-size:0px; border:0px; width: 100%" src="{{URL::to('/')}}/storage/images/{{$data['cover_img']}}" alt="img" />
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            <!--end img-->
                            <tr>
                              <td height="40"></td>
                            </tr>
                            <!--headline-->
                            <tr>
                              <td align="center" style="font-family: 'Open Sans', Arial, sans-serif; font-size: 22px;color:#414a51;font-weight: bold;line-height: 28px;">{{$data['header']}}</td>
                            </tr>
                            <!--end headline-->
                            <tr>
                              <td height="20"></td>
                            </tr>
                            <!--dotted-->
                            <tr>
                              <td align="center">
                                <table border="0" align="center" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td align="center">
                                      <table align="center" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td bgcolor="#ff5e3a" style="border-radius:5px;font-size:0px; line-height:0px;" height="5" width="5">&nbsp;</td>
                                        </tr>
                                      </table>
                                    </td>
                                    <td width="15"></td>
                                    <td align="center">
                                      <table align="center" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td bgcolor="#ff5e3a" style="border-radius:5px;font-size:0px; line-height:0px;" height="5" width="5">&nbsp;</td>
                                        </tr>
                                      </table>
                                    </td>
                                    <td width="15"></td>
                                    <td align="center">
                                      <table align="center" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td bgcolor="#ff5e3a" style="border-radius:5px;font-size:0px; line-height:0px;" height="5" width="5">&nbsp;</td>
                                        </tr>
                                      </table>
                                    </td>
                                    <td width="15"></td>
                                    <td align="center">
                                      <table align="center" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                          <td bgcolor="#ff5e3a" style="border-radius:5px;font-size:0px; line-height:0px;" height="5" width="5">&nbsp;</td>
                                        </tr>
                                      </table>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            <!--end dotted-->
                            <tr>
                              <td height="20"></td>
                            </tr>
                            <!--content-->
                            <tr>
                              <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
                                {{$data['text']}}
                              </td>
                            </tr>
                            <!--end content-->
                            @if(isset($data['reply_email']) && $data['reply_email'] != '')
                            <tr>
                              <td height="40"></td>
                            </tr>
                            <!--button-->
                            <tr>
                              <td align="center">
                                <table border="0" align="center" cellpadding="0" cellspacing="0" class="textbutton">
                                  <tr>
                                    <td bgcolor="#ff5e3a" class="btn-link" height="40" align="center" style="border-radius:4px;font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:14px;padding-left: 25px;padding-right: 25px;font-weight: bold; ">
                                      <a href="mailto:{{$data['reply_email']}}">{{l("Reply to", $data['lang'])}} {{$data['name']}}</a>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            @endif
                            <tr>
                              <td height="40"></td>
                            </tr>
                            <!--button-->
                            <tr>
                              <td align="center">
                                <table border="0" align="center" cellpadding="0" cellspacing="0" class="textbutton">
                                  <tr>
                                    <td bgcolor="#ff5e3a" class="btn-link" height="40" align="center" style="border-radius:4px;font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:14px;padding-left: 25px;padding-right: 25px;font-weight: bold; ">
                                      <a href="{{App::make('url')->to('/')}}/autoregister/{{md5($token.$data['email'])}}/{{$data['email']}}/{{$data['username']}}/{{$data['tracking']}}">{{l("Start chatting with", $data['lang'])}} {{$data['name']}}</a>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            
                            <!--end button-->
                            <tr>
                              <td height="5"></td>
                            </tr>
                            <!--detail-->
                            <!--end detail-->
                            <tr>
                              <td height="40"></td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                    <!--footer-->
                    <table class="table-inner" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="45" align="center" bgcolor="#f4f4f4" style="box-shadow:0px 3px 0px #ccd5dc; border-bottom-left-radius:6px; border-bottom-right-radius:6px;">
                          <a href="{{App::make('url')->to('/')}}/unsubscribe/{{$data['email']}}">Unsubscribe</a>
                        </td>
                      </tr>
                    </table>
                    <!--end footer-->
                    <!--social-->
                    <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td height="20"><img src="{{App::make('url')->to('/')}}/emailtracking/track/{{$data['tracking']}}" width="1px" height="1px"></td>
                      </tr>
                      
                      <tr>
                        <td height="40"></td>
                      </tr>
                    </table>
                    <!--end social-->
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>

</html>
