<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cảm ơn bạn đã mua sắm tại Florentic</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 40px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; max-width: 600px; width: 100%; border-radius: 4px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    
                    <tr>
                        <td align="center" style="padding: 40px 0 20px 0; border-bottom: 1px solid #eeeeee;">
                            <h1 style="margin: 0; font-size: 26px; letter-spacing: 4px; text-transform: uppercase; color: #000000; font-family: 'Georgia', serif;">
                                FLORENTIC
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 40px 30px 40px; text-align: center;">
                            <div style="color: #000000; font-size: 24px; letter-spacing: 5px; margin-bottom: 20px;">
                                ★★★★★
                            </div>

                            <h2 style="margin: 0 0 15px 0; font-size: 20px; font-weight: 600; color: #333333;">
                                Cảm ơn {{ $order->full_name }}!
                            </h2>
                            
                            <p style="margin: 0 0 20px 0; color: #666666; line-height: 1.6; font-size: 15px;">
                                Đơn hàng <strong>#{{ $order->order_code }}</strong> của bạn đã hoàn thành. Chúng tôi hy vọng bạn yêu thích sản phẩm mới từ Florentic cũng như cách chúng tôi phục vụ bạn.
                            </p>

                            <p style="margin: 0 0 30px 0; color: #666666; line-height: 1.6; font-size: 15px;">
                                Đánh giá của bạn là điều vô cùng quý giá, giúp chúng tôi hoàn thiện hơn mỗi ngày. Hãy chia sẻ cảm nhận của bạn nhé!
                            </p>

                            <div style="margin-bottom: 30px;">
                                <a href="{{ url('/reviews/' . $order->order_code) }}" style="background-color: #000000; color: #ffffff; padding: 14px 30px; text-decoration: none; font-weight: bold; border-radius: 2px; font-size: 14px; display: inline-block; text-transform: uppercase; letter-spacing: 1px;">
                                    Đánh giá sản phẩm ngay
                                </a>
                            </div>

                            <p style="font-size: 13px; color: #999; margin: 0; font-style: italic;">
                                (Việc đánh giá chỉ mất chưa đầy 1 phút)
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #fafafa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="margin: 0 0 5px 0; font-size: 12px; font-weight: bold; letter-spacing: 1px; color: #000;">FLORENTIC STUDIO</p>
                            <p style="margin: 0; font-size: 12px; color: #888888;">
                                Trân trọng cảm ơn bạn đã đồng hành.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>