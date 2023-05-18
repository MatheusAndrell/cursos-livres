<html>
<head>
    <script>
        var hash = "{{ $formData['hash'] }}";

        function submitPayuForm() {
            if (hash == '' || hash == null) {
                return;
            }
            var payuForm = document.forms.payuForm;
            payuForm.submit();
        }
    </script>
</head>
<body onload="submitPayuForm()" style="display: none;">
<form action="{{ $action }}" method="POST" name="payuForm">
    @csrf
    <input type="hidden" name="key" value="{{ $formData['key'] }}"/>
    <input type="hidden" name="hash" value="{{ $formData['hash'] }}"/>
    <input type="hidden" name="txnid" value="{{ $formData['txnid'] }}"/>
    <table>
        <tr>
            <td><b>Mandatory Parameters</b></td>
        </tr>
        <tr>
            <td>Amount:</td>
            <td><input name="amount" value="{{ $formData['amount'] }}"/></td>
            <td>First Name:</td>
            <td><input name="firstname" id="firstname"
                       value="{{ $formData['firstname'] }}"/></td>
        </tr>
        <tr>
            <td>Email:</td>
            <td><input name="email" id="email"
                       value="{{ $formData['email'] }}"/>
            </td>
            <td>Phone:</td>
            <td><input name="phone" value="{{ $formData['phone'] }}"/>
            </td>
        </tr>
        <tr>
            <td>Product Info:</td>
            <td colspan="3"><textarea
                    name="productinfo">{{ $formData['productinfo'] }}</textarea>
            </td>
        </tr>
        <tr>
            <td>Success URI:</td>
            <td colspan="3"><input name="surl"
                                   value="{{ $formData['surl'] }}"
                                   size="64"/></td>
        </tr>
        <tr>
            <td>Failure URI:</td>
            <td colspan="3"><input name="furl"
                                   value="{{ $formData['furl'] }}"
                                   size="64"/></td>
        </tr>

        <tr>
            <td colspan="3"><input type="hidden" name="service_provider" value="payu_paisa" size="64"/></td>
        </tr>

        <tr>
            <td><b>Optional Parameters</b></td>
        </tr>
        <tr>
            <td>Last Name:</td>
            <td><input name="lastname" id="lastname"
                       value="{{ $formData['lastname']??'' }}"/></td>
            <td>Cancel URI:</td>
            <td><input name="curl" value=""/></td>
        </tr>
        <tr>
            <td>Address1:</td>
            <td><input name="address1"
                       value="{{ $formData['address1']??'' }}"/>
            </td>
            <td>Address2:</td>
            <td><input name="address2"
                       value="{{ $formData['address2']??'' }}"/>
            </td>
        </tr>
        <tr>
            <td>City:</td>
            <td><input name="city" value="{{ $formData['city']??'' }}"/>
            </td>
            <td>State:</td>
            <td><input name="state"
                       value="{{ $formData['state']??'' }}"/>
            </td>
        </tr>
        <tr>
            <td>Country:</td>
            <td><input name="country"
                       value="{{ $formData['country']??'' }}"/></td>
            <td>Zipcode:</td>
            <td><input name="zipcode"
                       value="{{ $formData['zipcode']??'' }}"/></td>
        </tr>
        <tr>
            <td>UDF1:</td>
            <td><input name="udf1" value="{{ $formData['udf1']??'' }}"/></td>
            <td>UDF2:</td>
            <td><input name="udf2" value="{{ $formData['udf2']??'' }}"/></td>
        </tr>
        <tr>
            <td>UDF3:</td>
            <td><input name="udf3" value="{{ $formData['udf3']??'' }}"/></td>
            <td>UDF4:</td>
            <td><input name="udf4" value="{{ $formData['udf4']??'' }}"/></td>
        </tr>
        <tr>
            <td>UDF5:</td>
            <td><input name="udf5" value="{{ $formData['udf5']??'' }}"/></td>
            <td>PG:</td>
            <td><input name="pg" value="{{ $formData['pg']??'' }}"/></td>
        </tr>
        <tr>
            @if(!$formData['hash'])
                <td colspan="4"><input type="submit" value="Submit"/></td>
            @endif
        </tr>
    </table>
</form>
</body>
</html>
