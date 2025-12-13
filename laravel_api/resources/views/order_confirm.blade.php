<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng từ Florentic</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 40px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; max-width: 600px; width: 100%; border-radius: 4px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    
                    <tr>
                        <td align="center" style="padding: 40px 0 20px 0; border-bottom: 1px solid #eeeeee;">
                            <h1 style="margin: 0; font-size: 28px; letter-spacing: 4px; text-transform: uppercase; color: #000000; font-family: 'Georgia', serif;">
                                FLORENTIC
                            </h1>
                            <span style="font-size: 12px; color: #888; letter-spacing: 2px; text-transform: uppercase;">Premium Clothing</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 40px 20px 40px;">
                            <h2 style="margin: 0 0 15px 0; font-size: 20px; font-weight: 600; color: #333333;">
                                Cảm ơn {{ $order->full_name }},
                            </h2>
                            <p style="margin: 0 0 20px 0; color: #666666; line-height: 1.6; font-size: 14px;">
                                Đơn hàng của bạn tại <strong>Florentic</strong> đã được tiếp nhận và đang trong quá trình xử lý. Chúng tôi sẽ sớm gửi kiện hàng thời trang này đến tay bạn.
                            </p>

                            <div style="background-color: #f9f9f9; border: 1px solid #eeeeee; padding: 20px; border-radius: 4px; margin-bottom: 30px;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding-bottom: 10px; color: #888; font-size: 12px; text-transform: uppercase;">Mã đơn hàng</td>
                                        <td align="right" style="padding-bottom: 10px; font-weight: bold; color: #000;">#{{ $order->order_code }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 10px; border-top: 1px solid #e0e0e0; color: #333; font-weight: 600;">Tổng thanh toán</td>
                                        <td align="right" style="padding-top: 10px; border-top: 1px solid #e0e0e0; color: #000; font-weight: 700; font-size: 16px;">
                                            {{ number_format($order->total_amount) }} VNĐ
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div style="text-align: center; margin-bottom: 30px;">
                                <p style="margin-bottom: 15px; color: #666; font-size: 14px;">Theo dõi hành trình đơn hàng tại:</p>
                                <a href="{{ $trackingLink }}" style="background-color: #000000; color: #ffffff; padding: 14px 30px; text-decoration: none; font-weight: bold; border-radius: 2px; font-size: 14px; display: inline-block; text-transform: uppercase; letter-spacing: 1px;">
                                    Xem Đơn Hàng Của Tôi
                                </a>
                            </div>

                            <p style="margin: 0; font-size: 12px; color: #999999; line-height: 1.5; text-align: center;">
                                Nếu nút trên không hoạt động, vui lòng copy đường dẫn sau vào trình duyệt:<br>
                                <a href="{{ $trackingLink }}" style="color: #666; text-decoration: underline;">{{ $trackingLink }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #000000; color: #ffffff; padding: 30px; text-align: center;">
                            <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; letter-spacing: 2px;">FLORENTIC</p>
                            <p style="margin: 0 0 10px 0; font-size: 12px; color: #cccccc;">
                                Đẳng cấp thời trang của bạn.
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #666666;">
                                &copy; {{ date('Y') }} Florentic Store. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>