
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>تأكيد البريد الإلكتروني</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f3f4f6;
    font-family: Arial, Tahoma, sans-serif;
    color: #1f2937;
">
    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        style="background-color: #f3f4f6; padding: 30px 15px;"
    >
        <tr>
            <td align="center">
                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    style="
                        max-width: 600px;
                        background-color: #ffffff;
                        border-radius: 12px;
                        overflow: hidden;
                        border: 1px solid #e5e7eb;
                    "
                >
                    <tr>
                        <td
                            align="center"
                            style="
                                background-color: #f59e0b;
                                padding: 24px;
                                color: #ffffff;
                            "
                        >
                            <h1 style="margin: 0; font-size: 28px;">
                                SolarHub
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px;">
                            <h2
                                style="
                                    margin-top: 0;
                                    text-align: center;
                                    font-size: 24px;
                                "
                            >
                                تأكيد البريد الإلكتروني
                            </h2>

                            <p
                                style="
                                    font-size: 16px;
                                    line-height: 1.8;
                                    text-align: center;
                                "
                            >
                                استخدم رمز التحقق التالي لإكمال إنشاء حسابك:
                            </p>

                            <div
                                style="
                                    margin: 30px 0;
                                    padding: 20px;
                                    background-color: #fff7ed;
                                    border: 1px dashed #f59e0b;
                                    border-radius: 10px;
                                    text-align: center;
                                    direction: ltr;
                                "
                            >
                                <span
                                    style="
                                        font-size: 36px;
                                        font-weight: bold;
                                        letter-spacing: 10px;
                                        color: #b45309;
                                    "
                                >
                                    {{ $code }}
                                </span>
                            </div>

                            <p
                                style="
                                    font-size: 15px;
                                    line-height: 1.8;
                                    text-align: center;
                                "
                            >
                                تنتهي صلاحية الرمز خلال
                                <strong>{{ $expiresInMinutes }} دقائق</strong>.
                            </p>

                            <p
                                style="
                                    margin-top: 25px;
                                    font-size: 14px;
                                    line-height: 1.8;
                                    text-align: center;
                                    color: #6b7280;
                                "
                            >
                                إذا لم تطلب إنشاء هذا الحساب، يمكنك تجاهل الرسالة.
                                لا تشارك رمز التحقق مع أي شخص.
                            </p>

                            <hr
                                style="
                                    margin: 30px 0;
                                    border: 0;
                                    border-top: 1px solid #e5e7eb;
                                "
                            >

                            <p
                                dir="ltr"
                                style="
                                    margin-bottom: 0;
                                    font-size: 13px;
                                    line-height: 1.7;
                                    text-align: center;
                                    color: #9ca3af;
                                "
                            >
                                Use this verification code to complete your
                                SolarHub registration. Do not share it with anyone.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td
                            align="center"
                            style="
                                padding: 18px;
                                background-color: #f9fafb;
                                color: #9ca3af;
                                font-size: 12px;
                            "
                        >
                            &copy; {{ date('Y') }} SolarHub
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
