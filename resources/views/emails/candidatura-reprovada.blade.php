<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Candidatura Reprovada</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f2f2f2;">
    <table align="center" cellpadding="0" cellspacing="0" width="100%" style="padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; padding: 40px 30px; border-radius: 6px; font-family: Arial, sans-serif; box-shadow: 0 0 5px rgba(0,0,0,0.05);">
                    <tr>
                        <td align="center" style="padding-bottom: 20px;">
                            <img src="{{ asset('images/recruitPro-logo.png') }}" alt="RecruitPro" width="140" style="max-width: 100%; height: auto;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <h2 style="color: #555555; font-size: 20px; font-weight: normal; margin: 0;">Atualização sobre sua candidatura</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 16px; color: #444444; line-height: 1.7;">
                            {!! $conteudo !!}
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top: 30px; font-size: 12px; color: #999999; border-top: 1px solid #eeeeee;">
                            <p style="margin: 15px 0 5px;">© {{ date('Y') }} RecruitPro</p>
                            <a href="#" style="color: #999999; text-decoration: none; margin-right: 10px;">Termos</a>
                            <a href="#" style="color: #999999; text-decoration: none;">Privacidade</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
