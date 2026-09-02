<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
@php
    $main_color = App\Settings::where('id', 1)->firstOrFail();
    $token = env('AUTOREGISTER_TOKEN', '98eb7a12f25a609674cfe50a064a84c2');
  @endphp
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
a { color: {{$main_color->value}}; text-decoration: none; }
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
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                  <td height="50"></td>
                </tr>
                <!--Header Logo-->
                <!--end Header Logo-->
                <tr>
                  <td height="10"></td>
                </tr>
                <!--slogan-->
                <!--end slogan-->
                <tr>
                  <td height="10"></td>
                </tr>
              </table>
              <table class="table-inner" width="100%" border="0" cellspacing="0" cellpadding="0">
                <!--headline-->
                <tr>
                  <td height="50" align="center" bgcolor="{{$main_color->value}}" style=" border-top-left-radius:6px; border-top-right-radius: 6px;font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:20px;font-weight: bold;">{{l("User Registration")}}</td>
                </tr>
                <!--end headline-->
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!--end header-->
  <!--image-->
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td bgcolor="#FFFFFF" align="center">
                    <table class="table-inner" align="center" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td height="35"></td>
                      </tr>
                      <tr>
                        <td height="15"></td>
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
  <!--end image-->
  <!--content-->
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                  <td bgcolor="#FFFFFF" align="center">
                    <table class="table-inner" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="15"></td>
                      </tr>
                      <!--dotted-->
                      <!--end dotted-->
                      <tr>
                        <td height="15"></td>
                      </tr>
                      <!--content-->
                      <tr>
                        <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
                          {{l("Your registration details")}}
                        </td>
                      </tr>
                      <!--end content-->
                      <tr>
                        <td height="15"></td>
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
  <!--end content-->
  <!--list-->
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td bgcolor="#FFFFFF" height="15"></td>
                </tr>
                <tr>
                  <td align="center" bgcolor="#f8f8f8">
                    <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td height="25"></td>
                      </tr>
                      <tr>
                        <td align="center">
                          <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td align="left">
                                <table border="0" align="left" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td align="left" width="30">
                                      <table align="left" border="0" cellspacing="0" cellpadding="0">
                                        <!--number-->
                                        <tr>
                                          <td bgcolor="{{$main_color->value}}" height="30" width="30" align="center" style="border-radius:6px;font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:14px; padding-left: 10px;padding-right: 10px;font-weight: bold; ">1</td>
                                        </tr>
                                        <!--end number-->
                                      </table>
                                    </td>
                                    <!--detail-->
                                    <td align="left" style="font-family: 'Open sans', Arial, sans-serif; color:#414a51; font-size:15px;font-weight: normal; line-height: 28px;padding-left: 15px;">First Name: {{$data['firstname']}}</td>
                                    <!--end detail-->
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td height="20" style="border-bottom:1px solid #ebebeb;"></td>
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
  <!--end list-->
  <!--list-repeat-->
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="center" bgcolor="#f8f8f8">
                    <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td height="25"></td>
                      </tr>
                      <tr>
                        <td align="center">
                          <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td align="left">
                                <table border="0" align="left" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td align="left" width="30">
                                      <table align="left" border="0" cellspacing="0" cellpadding="0">
                                        <!--number-->
                                        <tr>
                                          <td bgcolor="{{$main_color->value}}" height="30" width="30" align="center" style="border-radius:6px;font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:14px; padding-left: 10px;padding-right: 10px;font-weight: bold; ">2</td>
                                        </tr>
                                        <!--end number-->
                                      </table>
                                    </td>
                                    <!--detail-->
                                    <td align="left" style="font-family: 'Open sans', Arial, sans-serif; color:#414a51; font-size:15px;font-weight: normal; line-height: 28px;padding-left: 15px;">Last Name: {{$data['lastname']}}</td>
                                    <!--end detail-->
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td height="20" style="border-bottom:1px solid #ebebeb;"></td>
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
  <!--end list-repeat-->
  <!--list-repeat-->
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="center" bgcolor="#f8f8f8">
                    <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td height="25"></td>
                      </tr>
                      <tr>
                        <td align="center">
                          <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td align="left">
                                <table border="0" align="left" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td align="left" width="30">
                                      <table align="left" border="0" cellspacing="0" cellpadding="0">
                                        <!--number-->
                                        <tr>
                                          <td bgcolor="{{$main_color->value}}" height="30" width="30" align="center" style="border-radius:6px;font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:14px; padding-left: 10px;padding-right: 10px;font-weight: bold; ">3</td>
                                        </tr>
                                        <!--end number-->
                                      </table>
                                    </td>
                                    <!--detail-->
                                    <td align="left" style="font-family: 'Open sans', Arial, sans-serif; color:#414a51; font-size:15px;font-weight: normal; line-height: 28px;padding-left: 15px;">Email: {{$data['email']}}</td>
                                    <!--end detail-->
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td height="20" style="border-bottom:1px solid #ebebeb;"></td>
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
  <!--end list-repeat-->
  <!--list-repeat-->
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="center" bgcolor="#f8f8f8">
                    <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td height="25"></td>
                      </tr>
                      <tr>
                        <td align="center">
                          <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td align="left">
                                <table border="0" align="left" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td align="left" width="30">
                                      <table align="left" border="0" cellspacing="0" cellpadding="0">
                                        <!--number-->
                                        <tr>
                                          <td bgcolor="{{$main_color->value}}" height="30" width="30" align="center" style="border-radius:6px;font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:14px; padding-left: 10px;padding-right: 10px;font-weight: bold; ">4</td>
                                        </tr>
                                        <!--end number-->
                                      </table>
                                    </td>
                                    <!--detail-->
                                    <td align="left" style="font-family: 'Open sans', Arial, sans-serif; color:#414a51; font-size:15px;font-weight: normal; line-height: 28px;padding-left: 15px;">Password: {{$data['password']}}</td>
                                    <!--end detail-->
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td height="20" style="border-bottom:1px solid #ebebeb;"></td>
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
  <!--end list-repeat-->
  <!--footer-->
  <table class="full" align="center" bgcolor="#eceff3" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                  <td height="25" bgcolor="#f8f8f8"></td>
                </tr>
                <tr>
                  <td align="center">
                    <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td width="30" align="right" valign="top">
                          <table width="30" border="0" align="right" cellpadding="0" cellspacing="0">
                            <tr>
                              <td height="25" bgcolor="#f8f8f8" style="border-bottom-left-radius:6px;font-size:0px;">&nbsp;</td>
                            </tr>
                            <tr>
                              <td height="25"></td>
                            </tr>
                          </table>
                        </td>
                        <td rowspan="2" align="center" background="images/cta-bg.png" style="background-image: url(images/cta-bg.png); background-repeat: repeat-x; background-size: auto; background-position: top;">
                          <table class="textbutton" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                            <!--Button-->
                            <tr>
                              <td bgcolor="{{$main_color->value}}" class="btn-link" width="540" height="50" align="center" style="border-radius:6px;padding-left: 15px;padding-right: 15px; font-family: 'Open Sans', Arial, sans-serif; font-size: 18px;color:#FFFFFF;font-weight: bold;">
                                @if(isset($data['verification']))
                                  <a href="{{App::make('url')->to('/')}}/autoregister/verify/{{$data['email']}}/{{$data['verification']}}">{{l("Verify Email")}}</a>
                                @else
                                  <a href="{{App::make('url')->to('/')}}/autoregister/{{$token}}/{{$data['email']}}/friends">{{l("Visit Website")}}</a>
                                @endif
                              </td>
                            </tr>
                            <!--end Button-->
                          </table>
                        </td>
                        <td width="30" align="left" valign="top">
                          <table width="30" border="0" align="left" cellpadding="0" cellspacing="0">
                            <tr>
                              <td height="25" bgcolor="#f8f8f8" style="border-bottom-right-radius:6px;font-size:0px;">&nbsp;</td>
                            </tr>
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
              <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td height="20"></td>
                </tr>
                <!--social-->
                <tr>
                  <td height="10"></td>
                </tr>
                <!--preference-->
                <!--end preference-->
                <tr>
                  <td height="55"></td>
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