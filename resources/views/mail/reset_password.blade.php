<!doctype html>
<html ⚡4email data-css-strict>

<head>
  <meta charset="utf-8">
  <style amp4email-boilerplate>
    body {
      visibility: hidden
    }
  </style>
  <script async src="https://cdn.ampproject.org/v0.js"></script>
</head>

<body class="body">
  <div dir="ltr" class="es-wrapper-color" lang="en">
    <table width="100%" cellspacing="0" cellpadding="0" class="es-wrapper">
      <tr>
        <td valign="top">

          <!-- Nội dung chính của email -->
          <table cellpadding="0" cellspacing="0" align="center" class="es-content">
            <tr>
              <td align="center">
                <table bgcolor="#fee0e0" align="center" cellpadding="0" cellspacing="0" width="600" class="es-content-body"
                  style="background-color:#fee0e0">
                  <tr>
                    <td align="left" bgcolor="#2e2b2b" style="background-color:#2e2b2b; padding: 20px;">
                      <h1 style="color:#13fe89; text-align:center;">Change your password!</h1>
                    </td>
                  </tr>
                  <tr>
                    <td align="left" bgcolor="#2e2c2c" style="background-color:#2e2c2c; padding: 20px; color: #fff;">
                      <p>Hi <strong>{{ $name }}</strong>,</p>
                      <p>You have requested to change your password at <strong>{{ $created }}</strong>.</p>
                      <p>To proceed, please use the following OTP code:</p>
                      <p><strong>YOUR OTP:</strong> <span style="font-size: 20px;">{{ $otp }}</span></p>
                      <p>⚠️ <strong>Note:</strong> Do not share this OTP with anyone.</p>
                      <p>This request will expire at <strong>{{ $expired }}</strong>.</p>
                      <p>
                        To reset your password, click here:
                        <a href="http://127.0.0.1:8000/api/auth/reset-password?token={{ $token }}" style="color: #00ffd0; font-weight:bold;">
                          Reset Password
                        </a>
                      </p>
                      <br />
                      <p style="font-size: 12px; color: #ccc;">— Admin of the app - GruntDev1204</p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>

        </td>
      </tr>
    </table>
  </div>
</body>

</html>
