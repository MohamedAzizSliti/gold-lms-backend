<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,700" rel="stylesheet">
    <style type="text/css">
        @media only screen and (max-width: 580px) {
            .m_wd_full {
                width: 100% !important;
                min-width: 100% !important;
                height: auto !important
            }

            .m_wd_full_db {
                width: 100% !important;
                min-width: 100% !important;
                height: auto !important;
                display: block;
            }

            .m_al {
                text-align: left !important
            }

            .m_db {
                display: block !important
            }

            .m_display_n {
                height: 20px !important;
                display: block;
            }

            .m_h10 {
                height: 10px !important;
                display: block;
            }

            .m_display_none {
                display: none;
            }

            .m_img_mc_fix {
                display: block !important;
                text-align: center !important;
            }
        }

        .wrapper-div {
            background: url({{url('/admin/img/email/body-bg-email.jpg')}}) no-repeat center;
            background-size: 100% 100%;
            background-position: center;
            background-attachment: fixed;
            background-position: top;
        }

        .title_footer {
            text-align: center;
            text-align: center;
            font-weight: 900;
            color:  #b1a80e;
            font-size: 14px;
            width: 47%;
            margin: 0 auto;
            padding: 9px;
            border-radius: 70px;
            background-color: #c60e220f;
            margin-bottom: 11px;
        }

        .footer strong {
            margin-left: 10px;
        }

        li {
            padding: 8px;
        }

        ul.fa-ul {
            list-style-type: none;
        }

        ul li strong {
            color:  #b1a80e;
        }

        .message_contact strong {
            color:  #b1a80e;
        }

        ul {
            display: inline-block;
            vertical-align: top;
            margin-top: 7px;
            padding-left: 16px;
            list-style-type: circle;
            list-style-position: outside;
        }

        li {
            padding-bottom: 1px;
        }

        .p-inline {
            display: inline-block;
            vertical-align: top;
        }
    </style>
</head>
<body style="margin:0px;padding:0px;background-color: rgb(223, 230, 247);">
<div style="margin:0px;padding:0px;background-color:#e4e5e7" class="wrapper-div">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <!-- LOGO START -->
        <tr>
            <td align="center" style="padding: 5px 5px 0 5px;">
                <table width="600" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="m_wd_full">
                    <tbody>
                    <tr>
                        <td>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr>
                                    <td height="30"><img
                                            src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif"
                                            alt="" width="1" style="width:1px;display:block"></td>
                                </tr>
                                <tr>
                                    <td align="center" class="m_img_mc_fix">
                                        <a href="" target="_blank">
                                            @php
                                                // $logo = env('APP_URL').'/public'. config('settings.logo');
                                            @endphp
                                            <img align="center" src="{{$logo}}" alt="logo" height=""
                                                 border="0" style="width:78px"> <strong
                                                style="color: #ad961b;position: relative !important;top: 9px !important; font-size: 30px;left: 20px !important"
                                            >GPS GOLD</strong>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="30"><img
                                            src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif"
                                            alt="" width="1" style="width:1px;display:block"></td>
                                </tr>
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
                <table width="600" border="0" cellspacing="0" cellpadding="0"

                       style="background: linear-gradient(90deg, #e0ad07 0%, #916f19 51%, rgb(145,121,25) 100%) 0% 0% / 200%;"
                       class="m_wd_full">
                    <tbody>
                    <tr>
                        <td>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr>
                                    <td height="50"><img
                                            src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif"
                                            alt="" width="1" style="width:1px;display:block"></td>
                                </tr>
                                <tr>
                                    <td align="center" class="m_img_mc_fix">
                                        <img align="center" src="{{url('/admin/img/email/signup.png')}}" alt="" border="0" style="height:115px;
                                            width: 115px;
                                             background-color: white;
                                            border-radius: 50%;
                                            background-color: rgba(255, 255, 255, 0.8705882352941177);
                                     ">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center"
                                        style="padding:25px 25px 0px 25px; font-family: 'PT Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:32px; font-weight:bold; color:#ffffff; line-height:30px; text-align:center; display:block;text-transform: uppercase;">

                                    </td>
                                </tr>
                                <tr>
                                    <td height="50"><img
                                            src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif"
                                            alt="" width="1" style="width:1px;display:block"></td>
                                </tr>
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
                <table width="600" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0"
                       style="background:#FFFFFF;" class="m_wd_full">
                    <tbody>
                    <tr>
                        <td style="padding: 0 25px 0 25px;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <!-- Button START -->
                                <tr>
                                    <td style="border-top:1px solid #eeeeee; padding:30px 0 0 0px;">
                                        <table align="center" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="background: linear-gradient(90deg, #e0b507 0%, #918b19 51%, rgb(145,129,25) 100%) 0% 0% / 200%;border-radius:5px;-webkit-border-radius:5px;-khtml-border-radius:5px;-moz-border-radius:5px;-o-border-radius:5px; display:block;">
                                                    <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                                        <tr>
                                                            <td>
                                                                <table cellspacing="0" cellpadding="0" border="0"
                                                                       width="100%">
                                                                    <tr>
                                                                        <td style="padding:14px 30px 14px 30px;text-transform:uppercase; font-family: 'PT Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align:center; font-size: 14px; font-weight:bold; line-height: 1.3; color:#ffffff;">
                                                                            <!-- Button -->
                                                                            <a href="test.com"
                                                                               style="color:#ffffff; text-decoration:none;cursor: pointer"
                                                                               target="_blank">Nouveau Compte</a>
                                                                        </td>
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
                <table width="600" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0"
                       style="background:#ffffff;" class="m_wd_full">
                    <tbody>
                    <tr>
                        <td style="padding:0 25px 0 25px;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr>
                                    <td height="50"><img
                                            src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif"
                                            alt="" width="1" style="width:1px;display:block"></td>
                                </tr>
                                <tr>
                                    <td class="message_contact" align="center"
                                        style="font-family: 'PT Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:14px; font-weight:normal; color:#24252a; line-height:22px; text-align:left; display:block;">
                                        <multiline>
                                            <p>
                                                Nous vous souhaitons la bienvenue chez <strong>GPS Gold </strong> et vous remercions de nous avoir choisis comme solution de <strong>tracking GPS</strong> pour vos véhicules.
                                            </p>
                                            <p>
                                                Afin de vous offrir la meilleure expérience, voici vos informations de connexion :
                                            </p>
                                            <h3>Accès à la plateforme Web</h3>
                                            <ul>
                                                <li>🔗 <strong>Lien : </strong><a href="https://gps-gold.com/admin/">https://gps-gold.com/admin/</a></li>
                                                <li>👤 <strong>Identifiant : </strong>{{$user->email}}</li>
                                                <li>🔑 <strong>Mot de passe : </strong>{{$clearedPassword}}</li>
                                            </ul>
                                            <h3>Accès à l’application mobile</h3>

                                            <ul>
                                                <li>👤 <strong>Identifiant : </strong>{{$user->phone}}</li>
                                                <li>🔑 <strong>Mot de passe : </strong>{{$clearedPassword}}</li>
                                            </ul>
                                            <h4>📥 Téléchargement de l’application mobile </h4>
                                            <p>
                                                Notre application est disponible sur Android et iOS. <br>
                                                Vous pouvez la télécharger en scannant le QR Code correspondant à votre téléphone.
                                            </p>
                                            <div style="text-align: center">
                                                <div style="display: inline-block;width: 48%">
                                                    <span style="display: block;text-align: center;font-weight: bold">🤖 Android   🤖</span>
                                                    <img src="{{url('/admin/img/email/qr_android.jpg')}}" alt="" width="250" height="250">
                                                </div>
                                                <div  style="display: inline-block;width: 48%">
                                                    <span style="display: block;text-align: center;font-weight: bold">📱 Iphone (Apple) : </span>
                                                    <img src="{{url('/admin/img/email/qr_iphone.jpg')}}" alt="" width="250" height="250">
                                                </div>
                                            </div>
                                            <p>
                                                Si vous avez la moindre question, notre équipe reste à votre disposition pour vous accompagner.
                                            </p>
                                            <strong style="display: block;text-align: center">Bienvenue à bord et bonne utilisation de GPS GOLD !</strong>
                                        </multiline>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="30"><img
                                            src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif"
                                            alt="" width="1" style="width:1px;display:block"></td>
                                </tr>
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
                <table width="600" bgcolor="#f6f7f9" border="0" cellspacing="0" cellpadding="0"
                       style="background:#f6f7f9;" class="m_wd_full">
                    <tbody>
                    <tr>
                        <td style="padding:0 25px 0 25px;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr>
                                    <td height="25"><img
                                            src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif"
                                            alt="" width="1" style="width:1px;display:block"></td>
                                </tr>
                                <tr class="footer">
                                    <td align="center"
                                        style="font-family: 'PT Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:13px; font-weight:normal; color:#24252a; line-height:19px; text-align:center; display:block;">
                                        <div class="title_footer">
                                             GPS GOLD
                                        </div>
                                        <strong>Adresse :</strong>  25 CITÉ EL MANAR - 2022 TUNISIE
                                        <strong>Téléphone :</strong> +216 27 253 737
                                        <strong>Courriel:</strong> contact@gps-gold.com
                                    </td>
                                </tr>
                                <tr>
                                    <td height="25"><img
                                            src="https://gallery.mailchimp.com/d942a4805f7cb9a8a6067c1e6/images/1a808f19-c541-48d8-afad-3d9529131c98.gif"
                                            alt="" width="1" style="width:1px;display:block"></td>
                                </tr>
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
