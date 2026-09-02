<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <!--[if !mso]><!-->
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!--<![endif]-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{$data['header']}}</title>
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
  <!--header-->
  <table class="full" width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#eceff3">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="440" align="center">
              <!--preference-->
              <table width="90%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td height="30"></td>
                </tr>
                <tr>
                  <td align="center" class="preference-link" style="font-family: 'Open sans', Arial, sans-serif; color:#95a5a6; font-size:11px; line-height: 28px;font-style: italic;">
                  </td>
                </tr>
                <tr>
                  <td height="10"></td>
                </tr>
              </table>
              <!--end preference-->
              <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="table-inner">
                <tr>
                  <td align="center" bgcolor="#414a51" style="background: linear-gradient( rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5) ), url({{$data['cover']}});background-size: 100%;border-bottom:5px solid #e0e5eb; background-position:top; border-top-left-radius:6px;border-top-right-radius:6px;">
                    <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0"> 
                      <tr>
                        <td height="35"></td>
                      </tr>
                      <!--Header Logo-->
                      <tr>
                        <td align="center" style="line-height: 0px;">
                          <img style="display:block; line-height:0px; font-size:0px; border:0px;" src="{{url('/img/logo.png')}}" alt="img" />
                        </td>
                      </tr>
                      <!--end Header Logo-->
                      <tr>
                        <td height="10"></td>
                      </tr>
                      <!--slogan-->
                      <tr>
                        <td align="center" style="font-family: 'Open Sans', Arial, sans-serif; color:#FFFFFF; font-size:13px; letter-spacing: 1px;line-height: 28px; font-style:italic;">{{l('New notification!', $data['lang'])}}</td>
                      </tr>
                      <!--end slogan-->
                      <tr>
                        <td height="25"></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!--end header-->
    <!--profile image-->
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="440" align="center">
              <table class="table-inner" align="center" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td bgcolor="#FFFFFF" align="center">
                    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="30"></td>
                      </tr>
                      <!--img-->
                      <tr>
                        <td align="center" style="line-height: 0px;">
                          <img src="{{$data['profile']}}" alt="img" style="display:block; line-height:0px; font-size:0px; border-radius:50%; border: 5px solid #eceff3; width: 100px; height: 100px;" />
                        </td>
                      </tr>
                      <!--end img-->
                      <tr>
                        <td height="15"></td>
                      </tr>
                      <!--name-->
                      <tr>
                        <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#ff5e3a; font-size:14px; line-height: 28px; font-weight: bold;font-style: italic;">{{$data['name']}}</td>
                      </tr>
                      <!--end name-->
                      <!--detail-->
                      <tr>
                        @if($data['age'] > 0)
                        <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#95a5a6; font-size:14px; line-height: 28px;font-style: italic;">{{$data['age']}}</td>
                        @endif
                      </tr>
                      <!--end detail-->
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>   
  </table>
  <!--end profile image-->
  <!--headline-->
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">  
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="440" align="center">
              <table align="center" class="table-inner" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td bgcolor="#FFFFFF" align="center">
                    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="35"></td>
                      </tr>
                      <!--title-->
                      <tr>
                        <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#3b3b3b; font-size:22px;font-weight: bold; line-height: 28px;">{{$data['header']}}</td>
                      </tr>
                      <!--end title-->
                      <tr>
                        <td height="10"></td>
                      </tr>
                      <!--Content-->
                      <tr>
                        <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
                          {{$data['text']}}
                        </td>
                      </tr>
                      <!--end Content-->
                      <tr>
                        <td height="25"></td>
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
                        <td style="text-align:center"><a href="{{App::make('url')->to('/referrals')}}/{{Auth::user()->username}}/{{md5(Auth::id().Auth::user()->email)}}" style="display:block; margin-top:30px; padding: 15px; background: #ff5e3a; border-radius: 10px; font-size: 16px; color: #fff; font-weight: 600;">{{l('Join now!', $data['lang'])}}</a></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!--end headline-->
  <!--footer-->
  <table class="full" width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#eceff3">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="440" align="center">
              <table class="table-inner" align="center" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td height="30" bgcolor="#FFFFFF" style="border-bottom-left-radius:6px;border-bottom-right-radius:6px; box-shadow:0px 3px 0px #e0e5eb;"></td>
                </tr>
                <tr>
                  <td height="25"></td>
                </tr>
               <tr>
                <td height="35"></td>
               </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!--end footer-->
</body>

</html>