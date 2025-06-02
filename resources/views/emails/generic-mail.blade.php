<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,700" rel="stylesheet">
    <style type="text/css">
        @media only screen and (max-width:580px){
            .m_wd_full {
                width:100%!important;
                min-width:100%!important;
                height:auto!important
            }
            .m_wd_full_db {
                width:100%!important;
                min-width:100%!important;
                height:auto!important;
                display:block;
            }
            .m_al {
                text-align:left!important
            }
            .m_db {
                display:block!important
            }
            .m_display_n {
                height:20px!important;
                display:block;
            }
            .m_h10 {
                height:10px!important;
                display:block;
            }
            .m_display_none {
                display:none;
            }
            .m_img_mc_fix {
                display:block!important;
                text-align:center!important;
            }
        }

        .wrapper-div{
            background: url({{url('/img/email/body-bg-email.jpg')}}) no-repeat center;
            background-size: 100% 100%;
            background-position: center;
            background-attachment: fixed;
            background-position: top;
            background-color: #811d3a;
            background-image: linear-gradient(to right, #1e335c 0, #e30617 100%) !important;
        }

        .title_footer {
            text-align: center;
            font-weight: 900;
            color: #b91026;
            font-size: 14px;
            width: 19%;
            margin: 13px auto;
            padding: 9px;
            border-radius: 70px;
            background-color: #ffffff;
            margin-bottom: 11px;
        }

        .footer strong{
            margin-left: 10px;
            color: white;
            font-size: 10.5pt;
            font-family: Helvetica, sans-serif, serif, EmojiFont;
        }

        li {
            padding: 8px;
        }

        ul.fa-ul {
            list-style-type: none;
        }

        ul li strong {
            color: #8232fc;
        }

        .message_contact strong {
            color: #8232fc;
        }
        ul{
            display: inline-block;
            vertical-align: top;
            margin-top: 7px;
            padding-left: 16px;
            list-style-type: circle;
            list-style-position: outside;
        }
        li{
            padding-bottom: 1px;
        }
        .p-inline{
            display: inline-block;
            vertical-align: top;
        }
        body{
            background-color: #811d3a;
            background-image: linear-gradient(to right, #1e335c 0, #e30617 100%) !important;
        }
    </style>
</head>
<body style="margin:0px;padding:0px;background-color: rgb(223, 230, 247);">
<div style="margin:0px;padding:0px;background-color:#e4e5e7" class="wrapper-div">
    @php
        $backgroundColorContent = "#1e335c";
    @endphp
    <table width="100%" border="0" cellspacing="0" cellpadding="0" >
        <tbody>
        <!-- LOGO START -->
        <tr>
            <td align="center" style="padding: 5px 5px 0 5px;">
                <table width="600" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="m_wd_full">
                    <tbody>
                    <tr>
                        <td>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: {{$backgroundColorContent}};">
                                <tbody>
                                <tr>
                                    <td height="30"><img src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif" alt="" width="1" style="width:1px;display:block"></td></tr>
                                <tr>
                                    <td align="center" class="m_img_mc_fix">
                                        <a href="" target="_blank">
                                            {{--                                            <img align="center" src="{{url('img/automobile/autooccasion/email/logo.png')}}" alt="" width="" height="" border="0" style="width:240px"  >--}}
                                            <img align="center" src="" alt="logo frenchLocker"  height="" border="0" style="width:263px"  >
                                        </a>
                                    </td>
                                </tr>
                                <tr><td height="30">
                                        <p style="color: white;text-align: center;margin-top: 45px;font-family: Helvetica, sans-serif, serif, EmojiFont;">
                                            LA CONSIGNE DU CLICK & COLLECT MUTUALISÉ
                                        </p>
                                    </td></tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <!-- LOGO END -->
        <!-- HEADING + ICON START -->

        <tr>
            <td align="center" style="padding: 0 5px 0 5px;">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: {{$backgroundColorContent}};" class="m_wd_full">
                    <tbody>
                    <tr>
                        <td>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" >
                                <tbody>
                                <tr><td height="50"> </td></tr>
                                <tr>
                                    <td align="center" class="m_img_mc_fix">
                                        <div style="background-color: white;width: 92%;height: 84px;">
                                            <p style="text-align: center;color: {{$backgroundColorContent}};
                                                line-height: 1.5;
                                                position: relative;
                                                padding-top: 17px;
                                                font-size: 13.5pt;
                                                margin-top: 0px;
                                                font-family: Helvetica, sans-serif, serif, EmojiFont;">
                                                Hello ! <br>
                                                Comment ça va aujourd'hui ?
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding:25px 25px 0px 25px; font-family: 'PT Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:32px; font-weight:bold; color:#ffffff; line-height:30px; text-align:center; display:block;text-transform: uppercase;">

                                    </td>
                                </tr>
                                <tr><td height="50">
                                        <p style="color: white;text-align: center;font-size: 13.5pt;font-family: Helvetica, sans-serif, serif, EmojiFont;">
                                            Votre compte a été modifié !
                                        </p>
                                    </td></tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <!-- HEADING + ICON END -->

        <!-- HEADING + ICON START -->
        <tr>
            <td align="center" style="padding: 0 5px 0 5px;">
                <table width="600" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0" style="background-color: {{$backgroundColorContent}};" class="m_wd_full">
                    <tbody>
                    <tr>
                        <td style="padding: 0 25px 0 25px;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <!-- Button START -->
                                <tr>
                                    <td style="padding:30px 0 0 0px;">
                                        <table align="center" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="background-color: {{$backgroundColorContent}};border-radius:5px;-webkit-border-radius:5px;-khtml-border-radius:5px;-moz-border-radius:5px;-o-border-radius:5px; display:block;">
                                                    <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                                        <tr>
                                                            <td>
                                                                <img src="/public/img/sketch.png">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                                                    <tr>
                                                                        <td>
                                                                            <p style="color: white;text-align: center;font-size: 13.5pt;font-family: Helvetica, sans-serif, serif, EmojiFont;line-height: 2.1;padding-top: 6px">
                                                                                Votre email : bensassiridha1@gmail.com <br>
                                                                                Votre mot de passe : ridhaIsi2017
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:14px 30px 14px 30px;text-transform:uppercase; font-family: 'PT Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align:center; font-size: 14px; font-weight:bold; line-height: 1.3; color:#ffffff;">
                                                                <!-- Button -->
                                                                <a href="#" style="color:#ffffff; text-decoration:none;cursor: pointer;    background-color: #cd0b1f;
    padding: 14px;
    border-radius: 10px;" target="_blank">Compléter mon profile</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                </tr>
                                <!-- Button END -->
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <!-- HEADING + ICON END -->


        <!-- ACCOUNT INFORMATION START -->
        <tr>
            <td align="center" style="padding:0 5px 0 5px;">
                <table width="600" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" style="background-color: {{$backgroundColorContent}};" class="m_wd_full">
                    <tbody>
                    <tr>
                        <td style="padding:0 25px 0 25px;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr><td height="50"><img src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif" alt="" width="1" style="width:1px;display:block"></td></tr>
                                <tr>
                                    <td class="message_contact" align="center" style="font-family: 'PT Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:14px; font-weight:normal; color:#24252a; line-height:22px; text-align:left; display:block;">
                                        <multiline>
                                            {{--                                            <p>   votre message ici</p>--}}
                                        </multiline>
                                    </td>
                                </tr>
                                <tr><td height="30"><img src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif" alt="" width="1" style="width:1px;display:block"></td></tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <!-- ACCOUNT INFORMATION END -->

        <!-- Footer -->
        <tr>
            <td align="center" style="padding:0 5px 5px 5px;">
                <table width="600" bgcolor="#f6f7f9" border="0" cellspacing="0" cellpadding="0" style="background: #19253e;
                    -webkit-box-shadow: 0px -8px 10px -8px #000000;
                    box-shadow: 0px -8px 10px -8px #000000;
                    color: white;" class="m_wd_full">
                    <tbody>
                    <tr>
                        <td style="padding:0 25px 0 25px;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr><td height="25"><img src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif" alt="" width="1" style="width:1px;display:block"></td></tr>
                                <tr class="footer">
                                    <td align="center" style="font-family: 'PT Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:13px; font-weight:normal; color:#24252a; line-height:19px; text-align:center; display:block;">
                                        <strong>Besoin d'aide ? <a href="mailto:support@frenchlockerhelp.zendesk.com" style="color: white">support@frenchlockerhelp.zendesk.com</a></strong>
                                        <div class="title_footer">
                                            À BIENTÔT
                                        </div>
                                    </td>
                                </tr>
                                <tr><td height="25"><img src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif" alt="" width="1" style="width:1px;display:block"></td></tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <!-- Footer END -->

        </tbody>
    </table>
</div>
</body>
</html>
