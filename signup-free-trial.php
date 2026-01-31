<? require_once("global/config.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Home | <?=$title?></title>
	
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
   <? require_once("css.php"); ?>
</head>

<body class="horizontal-nav skin-default card-no-border">
    <? require_once("loader.php"); ?>
    <section id="wrapper">
        <? require_once("menu.php"); ?>
        
        <div class="position-relative">
            <div class="diamond-signup">
                <div class="card-body d-flex-wrap">
                    <div class="col-12 col-sm-6">
                        <h1>Unlock our DIAMOND ED TECH demos and all of our resources.</h1>
                        <p><b>See how to handle leads, enroll remotely, and streamline your entire student management processes.</b></p>
                        <p><b>Watch our demos to see how we can help you:</b></p>
                        <ul class="list-tick">
                            <li>Build engaging campaigns that quickly convert leads into students</li>
                            <li>Enrollment students with an organized, repeatable process</li>
                            <li>Track, prioritize, and respond to issues on every channel</li>
                            <li>Grow your way with customization, ready-to-go apps, free trainings, live experts, and more</li>
                        </ul>
                        <p>And when you're ready for a trial, there's no credit card required and no software to install.</p>
                        <p><b>Questions? Talk to an expert: XXX XXXX XXXX</b></p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="signup-form-sec">
                            <form>                               
                               <div class="headingComponent parbase section">
                                  <h3>
                                     <span class="header-text">
                                        Sign up now to start your free sales trial.
                                     </span>
                                  </h3>
                               </div>
                               <div class="fields-container section">
                                  <div class="fields-wrapper clearfix d-flex align-items-center justify-content-between">
                                     <div class="field-container-50 mr-2">
                                        <div class="firstName textFieldInput">
                                           <div class="field  ">
                                                <label for="FIRST_NAME" class="placeholder">
                                              First name
                                              </label>
                                              <input id="FIRST_NAME" class="form-control required-entry" type="text" name="FIRST_NAME" placeholder="First name">
                                           </div>
                                        </div>
                                     </div>
                                     <div class="field-container-50">
                                        <div class="lastName textFieldInput">
                                           <div class="field  ">
                                                <label for="LAST_NAME" class="placeholder">
                                              Last name
                                              </label>
                                                <input id="LAST_NAME" class="form-control required-entry" type="text" name="LAST_NAME" placeholder="Last name">
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                               </div>
                               <div class="emailInput textFieldInput section">
                                  <div class="field">
                                        <label for="UserEmail-YXsM" class="placeholder">
                                     Email
                                     </label>
                                     <input type="email" name="UserEmail" class="form-control required-entry" placeholder="Email">
                                     <span class="error-msg" id="UserEmail-YXsM-errMsg">Enter a valid email address</span>
                                  </div>
                               </div>
                               <div class="userTitle selectFieldInput section">
                                  <div class="field">
                                    <label for="UserTitle-E9Pm" class="placeholder">Job Title</label>
                                     <select id="UserTitle-E9Pm" name="UserTitle" class="form-control required-entry">
                                        <option disabled="" label="Job Title" selected="" value="">Job Title</option>
                                        <option value="Sales_Manager_AP">Sales Manager</option>
                                        <option value="Marketing_PR_Manager_AP">Marketing / PR Manager</option>
                                        <option value="Customer_Service_Manager_AP">Customer Service Manager</option>
                                        <option value="CxO_General_Manager_AP">CxO / General Manager</option>
                                        <option value="IT_Manager_AP">IT Manager</option>
                                        <option value="Operations_Manager_AP">Operations Manager</option>
                                        <option value="Others_AP">Others</option>
                                        <option value="Personal_Interest_AP">Personal Interest </option>
                                     </select>
                                     <span class="error-msg" id="UserTitle-E9Pm-errMsg">Select your title</span>
                                  </div>
                               </div>
                               <div class="phoneInput textFieldInput section">
                                  <div class="field">
                                    <label for="UserPhone-w2HV" class="placeholder">
                                     Phone
                                     </label>
                                     <input id="UserPhone-w2HV" type="tel" class="form-control required-entry" placeholder="Phone">
                                     <span class="error-msg" id="UserPhone-w2HV-errMsg">Enter a valid phone number</span>
                                  </div>
                               </div>
                               <div class="company textFieldInput section">
                                  <div class="field" data-db-field="">
                                    <label for="CompanyName-UKgG" class="placeholder">
                                     Company
                                     </label>
                                     <input id="CompanyName-UKgG" type="text" name="CompanyName" class="form-control required-entry" placeholder="Company">
                                     <span class="error-msg" id="CompanyName-UKgG-errMsg">Enter your company name</span>
                                  </div>
                               </div>
                               <div class="selectFieldInput section">
                                  <div class="field">
                                    <label for="CompanyEmployees-T6S1" class="placeholder">Employees</label>
                                     <select id="CompanyEmployees-T6S1" name="CompanyEmployees" class="form-control required-entry">
                                        <option disabled="" label="Employees" selected="" value="">Employees</option>
                                        <option value="9" data-field-map="{&quot;FormCampaignId&quot;:&quot;7010M000000uj7gQAA&quot;,&quot;formName&quot;:&quot;Sales_Essentials_Trial_14&quot;,&quot;PartnerPromoCode&quot;:&quot;Sales Essentials&quot;,&quot;Lead.Primary_Product_Interest__c&quot;:&quot;Sales Essentials&quot;}">1 - 15 employees</option>
                                        <option value="75" data-field-map="{&quot;FormCampaignId&quot;:&quot;7010M000000uj7lQAA&quot;,&quot;formName&quot;:&quot;New_PE_Trial&quot;,&quot;PartnerPromoCode&quot;:&quot;&quot;,&quot;Lead.Primary_Product_Interest__c&quot;:&quot;Sales&quot;}">16 - 100 employees</option>
                                        <option value="250" data-field-map="{&quot;FormCampaignId&quot;:&quot;7010M000000uj7lQAA&quot;,&quot;formName&quot;:&quot;New_PE_Trial&quot;,&quot;PartnerPromoCode&quot;:&quot;&quot;,&quot;Lead.Primary_Product_Interest__c&quot;:&quot;Sales&quot;}">101 - 500 employees</option>
                                        <option value="950" data-field-map="{&quot;FormCampaignId&quot;:&quot;7010M000000uj7lQAA&quot;,&quot;formName&quot;:&quot;New_PE_Trial&quot;,&quot;PartnerPromoCode&quot;:&quot;&quot;,&quot;Lead.Primary_Product_Interest__c&quot;:&quot;Sales&quot;}">501 - 1500 employees</option>
                                        <option value="1600" data-field-map="{&quot;FormCampaignId&quot;:&quot;7010M000000uj7lQAA&quot;,&quot;formName&quot;:&quot;New_PE_Trial&quot;,&quot;PartnerPromoCode&quot;:&quot;&quot;,&quot;Lead.Primary_Product_Interest__c&quot;:&quot;Sales&quot;}">1501+ employees</option>
                                     </select>
                                     <span class="error-msg" id="CompanyEmployees-T6S1-errMsg">Select the number of employees</span>
                                  </div>
                               </div>
                               <div class="country-state-group section">
                                  <div class="cntry-wrap section">
                                     <div class="country_field selectFieldInput">
                                        <div class="field valid">
                                            <label for="CompanyCountry-2AnV" class="placeholder">Country</label>
                                           <select id="CompanyCountry-2AnV" name="CompanyCountry" class="form-control required-entry">
                                              <option disabled="" label="Country" selected="" value="">Country</option>
                                              <option value="US">United States</option>
                                              <option value="AF">Afghanistan</option>
                                              <option value="AL">Albania</option>
                                              <option value="DZ">Algeria</option>
                                              <option value="AS">American Samoa</option>
                                              <option value="AD">Andorra</option>
                                              <option value="AI">Anguilla</option>
                                              <option value="AQ">Antarctica</option>
                                              <option value="AG">Antigua And Barbuda</option>
                                              <option value="AR">Argentina</option>
                                              <option value="AM">Armenia</option>
                                              <option value="AW">Aruba</option>
                                              <option value="AU">Australia</option>
                                              <option value="AT">Austria</option>
                                              <option value="AZ">Ayerbaijan</option>
                                              <option value="BS">Bahamas, The</option>
                                              <option value="BH">Bahrain</option>
                                              <option value="BD">Bangladesh</option>
                                              <option value="BB">Barbados</option>
                                              <option value="BY">Belarus</option>
                                              <option value="BZ">Belize</option>
                                              <option value="BE">Belgium</option>
                                              <option value="BJ">Benin</option>
                                              <option value="BM">Bermuda</option>
                                              <option value="BT">Bhutan</option>
                                              <option value="BO">Bolivia</option>
                                              <option value="BV">Bouvet Is</option>
                                              <option value="BA">Bosnia and Herzegovina</option>
                                              <option value="BW">Botswana</option>
                                              <option value="BR">Brazil</option>
                                              <option value="IO">British Indian Ocean Territory</option>
                                              <option value="BN">Brunei</option>
                                              <option value="BG">Bulgaria</option>
                                              <option value="BF">Burkina Faso</option>
                                              <option value="BI">Burundi</option>
                                              <option value="KH">Cambodia</option>
                                              <option value="CM">Cameroon</option>
                                              <option value="CA">Canada</option>
                                              <option value="CV">Cape Verde</option>
                                              <option value="KY">Cayman Is</option>
                                              <option value="CF">Central African Republic</option>
                                              <option value="TD">Chad</option>
                                              <option value="CL">Chile</option>
                                              <option value="CN">China</option>
                                              <option value="HK">Hong Kong</option>
                                              <option value="MO">Macau</option>
                                              <option value="CX">Christmas Is</option>
                                              <option value="CC">Cocos (Keeling) Is</option>
                                              <option value="CO">Colombia</option>
                                              <option value="KM">Comoros</option>
                                              <option value="CK">Cook Islands</option>
                                              <option value="CR">Costa Rica</option>
                                              <option value="CI">Cote D'Ivoire (Ivory Coast)</option>
                                              <option value="HR">Croatia (Hrvatska)</option>
                                              <option value="CY">Cyprus</option>
                                              <option value="CZ">Czech Republic</option>
                                              <option value="CD">Democratic Republic of the Congo</option>
                                              <option value="DK">Denmark</option>
                                              <option value="DM">Dominica</option>
                                              <option value="DO">Dominican Republic</option>
                                              <option value="DJ">Djibouti</option>
                                              <option value="EC">Ecuador</option>
                                              <option value="EG">Egypt</option>
                                              <option value="SV">El Salvador</option>
                                              <option value="GQ">Equatorial Guinea</option>
                                              <option value="ER">Eritrea</option>
                                              <option value="EE">Estonia</option>
                                              <option value="ET">Ethiopia</option>
                                              <option value="FK">Falkland Is (Is Malvinas)</option>
                                              <option value="FO">Faroe Islands</option>
                                              <option value="FJ">Fiji Islands</option>
                                              <option value="FI">Finland</option>
                                              <option value="FR">France</option>
                                              <option value="GF">French Guiana</option>
                                              <option value="PF">French Polynesia</option>
                                              <option value="TF">French Southern Territories</option>
                                              <option value="MK">F.Y.R.O. Macedonia</option>
                                              <option value="GA">Gabon</option>
                                              <option value="GM">Gambia, The</option>
                                              <option value="GE">Georgia</option>
                                              <option value="DE">Germany</option>
                                              <option value="GH">Ghana</option>
                                              <option value="GI">Gibraltar</option>
                                              <option value="GR">Greece</option>
                                              <option value="GL">Greenland</option>
                                              <option value="GD">Grenada</option>
                                              <option value="GP">Guadeloupe</option>
                                              <option value="GU">Guam</option>
                                              <option value="GT">Guatemala</option>
                                              <option value="GN">Guinea</option>
                                              <option value="GW">Guinea-Bissau</option>
                                              <option value="GY">Guyana</option>
                                              <option value="HT">Haiti</option>
                                              <option value="HM">Heard and McDonald Is</option>
                                              <option value="HN">Honduras</option>
                                              <option value="HU">Hungary</option>
                                              <option value="IS">Iceland</option>
                                              <option value="IN">India</option>
                                              <option value="ID">Indonesia</option>
                                              <option value="IE">Ireland</option>
                                              <option value="IL">Israel</option>
                                              <option value="IT">Italy</option>
                                              <option value="JM">Jamaica</option>
                                              <option value="JP">Japan</option>
                                              <option value="JO">Jordan</option>
                                              <option value="KZ">Kayakhstan</option>
                                              <option value="KE">Kenya</option>
                                              <option value="KI">Kiribati</option>
                                              <option value="KR">Korea, South</option>
                                              <option value="KW">Kuwait</option>
                                              <option value="KG">Kyrgyzstan</option>
                                              <option value="LA">Laos</option>
                                              <option value="LV">Latvia</option>
                                              <option value="LB">Lebanon</option>
                                              <option value="LS">Lesotho</option>
                                              <option value="LR">Liberia</option>
                                              <option value="LI">Liechtenstein</option>
                                              <option value="LT">Lithuania</option>
                                              <option value="LU">Luxembourg</option>
                                              <option value="MG">Madagascar</option>
                                              <option value="MW">Malawi</option>
                                              <option value="MY">Malaysia</option>
                                              <option value="MV">Maldives</option>
                                              <option value="ML">Mali</option>
                                              <option value="MT">Malta</option>
                                              <option value="MH">Marshall Is</option>
                                              <option value="MR">Mauritania</option>
                                              <option value="MU">Mauritius</option>
                                              <option value="MQ">Martinique</option>
                                              <option value="YT">Mayotte</option>
                                              <option value="MX">Mexico</option>
                                              <option value="FM">Micronesia</option>
                                              <option value="MD">Moldova</option>
                                              <option value="MC">Monaco</option>
                                              <option value="MN">Mongolia</option>
                                              <option value="MS">Montserrat</option>
                                              <option value="MA">Morocco</option>
                                              <option value="MZ">Mozambique</option>
                                              <option value="MM">Myanmar</option>
                                              <option value="NA">Namibia</option>
                                              <option value="NR">Nauru</option>
                                              <option value="NP">Nepal</option>
                                              <option value="NL">Netherlands, The</option>
                                              <option value="AN">Netherlands Antilles</option>
                                              <option value="NC">New Caledonia</option>
                                              <option value="NZ">New Zealand</option>
                                              <option value="NI">Nicaragua</option>
                                              <option value="NE">Niger</option>
                                              <option value="NG">Nigeria</option>
                                              <option value="NU">Niue</option>
                                              <option value="NO">Norway</option>
                                              <option value="NF">Norfolk Island</option>
                                              <option value="MP">Northern Mariana Is</option>
                                              <option value="OM">Oman</option>
                                              <option value="PK">Pakistan</option>
                                              <option value="PW">Palau</option>
                                              <option value="PA">Panama</option>
                                              <option value="PG">Papua new Guinea</option>
                                              <option value="PY">Paraguay</option>
                                              <option value="PE">Peru</option>
                                              <option value="PH">Philippines</option>
                                              <option value="PN">Pitcairn Island</option>
                                              <option value="PL">Poland</option>
                                              <option value="PT">Portugal</option>
                                              <option value="PR">Puerto Rico</option>
                                              <option value="QA">Qatar</option>
                                              <option value="CG">Republic of the Congo</option>
                                              <option value="RE">Reunion</option>
                                              <option value="RO">Romania</option>
                                              <option value="RU">Russia</option>
                                              <option value="RW">Rwanda</option>
                                              <option value="SH">Saint Helena</option>
                                              <option value="KN">Saint Kitts And Nevis</option>
                                              <option value="LC">Saint Lucia</option>
                                              <option value="PM">Saint Pierre and Miquelon</option>
                                              <option value="VC">Saint Vincent And The Grenadines</option>
                                              <option value="WS">Samoa</option>
                                              <option value="SM">San Marino</option>
                                              <option value="ST">Sao Tome and Principe</option>
                                              <option value="SA">Saudi Arabia</option>
                                              <option value="SN">Senegal</option>
                                              <option value="rs">Serbia</option>
                                              <option value="SC">Seychelles</option>
                                              <option value="SL">Sierra Leone</option>
                                              <option value="SG">Singapore</option>
                                              <option value="SK">Slovakia</option>
                                              <option value="SI">Slovenia</option>
                                              <option value="SB">Solomon Islands</option>
                                              <option value="SO">Somalia</option>
                                              <option value="ZA">South Africa</option>
                                              <option value="GS">South Georgia &amp; The S. Sandwich Is</option>
                                              <option value="ES">Spain</option>
                                              <option value="LK">Sri Lanka</option>
                                              <option value="SR">Suriname</option>
                                              <option value="SJ">Svalbard And Jan Mayen Is</option>
                                              <option value="SZ">Swaziland</option>
                                              <option value="SE">Sweden</option>
                                              <option value="CH">Switzerland</option>
                                              <option value="TW">Taiwan</option>
                                              <option value="TJ">Tajikistan</option>
                                              <option value="TZ">Tanzania</option>
                                              <option value="TH">Thailand</option>
                                              <option value="TL">Timor-Leste</option>
                                              <option value="TG">Togo</option>
                                              <option value="TK">Tokelau</option>
                                              <option value="TO">Tonga</option>
                                              <option value="TT">Trinidad And Tobago</option>
                                              <option value="TN">Tunisia</option>
                                              <option value="TR">Turkey</option>
                                              <option value="TC">Turks And Caicos Is</option>
                                              <option value="TM">Turkmenistan</option>
                                              <option value="TV">Tuvalu</option>
                                              <option value="UG">Uganda</option>
                                              <option value="UA">Ukraine</option>
                                              <option value="AE">United Arab Emirates</option>
                                              <option value="GB">United Kingdom</option>
                                              <option value="UM">United States Minor Outlying Is</option>
                                              <option value="UY">Uruguay</option>
                                              <option value="UZ">Uzbekistan</option>
                                              <option value="VU">Vanuatu</option>
                                              <option value="VA">Vatican City State (Holy See)</option>
                                              <option value="VE">Venezuela</option>
                                              <option value="VN">Vietnam</option>
                                              <option value="VG">Virgin Islands (British)</option>
                                              <option value="VI">Virgin Islands (US)</option>
                                              <option value="WF">Wallis And Futuna Islands</option>
                                              <option value="EH">Western Sahara</option>
                                              <option value="YE">Yemen</option>
                                              <option value="ZM">Zambia</option>
                                              <option value="ZW">Zimbabwe</option>
                                           </select>
                                           <span class="error-msg" id="CompanyCountry-2AnV-errMsg">Choose a valid country</span>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="state-wrap section" style="display: none;">
                                     <div class="state_field selectFieldInput">
                                        <div class="field">
                                            <label for="CompanyState-85Ae" class="placeholder">State</label>
                                           <select id="CompanyState-85Ae" name="CompanyState" class="form-control required-entry">
                                              <option disabled="" label="State" selected="" value="">State</option>
                                              <option value="Andaman_and_Nicobar_Islands">Andaman and Nicobar Islands</option>
                                              <option value="Andhrapradesh">Andhra Pradesh</option>
                                              <option value="Arunachal_Pradesh">Arunachal Pradesh</option>
                                              <option value="Assam">Assam</option>
                                              <option value="Bihar">Bihar</option>
                                              <option value="Chandigarh">Chandigarh</option>
                                              <option value="Chhattisgarh">Chhattisgarh</option>
                                              <option value="Dadra_and_Nagar_Haveli_and_Daman_and_Diu">Dadra and Nagar Haveli and Daman and Diu</option>
                                              <option value="Delhi">Delhi</option>
                                              <option value="Goa">Goa</option>
                                              <option value="Gujarat">Gujarat</option>
                                              <option value="Haryana">Haryana</option>
                                              <option value="Himachal_Pradesh">Himachal Pradesh</option>
                                              <option value="Jammu_and_Kashmir">Jammu and Kashmir</option>
                                              <option value="Jharkhand">Jharkhand</option>
                                              <option value="Karnataka">Karnataka</option>
                                              <option value="Kerala">Kerala</option>
                                              <option value="Ladakh">Ladakh</option>
                                              <option value="Lakshadweep">Lakshadweep</option>
                                              <option value="Madhya_Pradesh">Madhya Pradesh</option>
                                              <option value="Maharashtra">Maharashtra</option>
                                              <option value="Manipur">Manipur</option>
                                              <option value="Meghalaya">Meghalaya</option>
                                              <option value="Mizoram">Mizoram</option>
                                              <option value="Nagaland">Nagaland</option>
                                              <option value="Odisha">Odisha</option>
                                              <option value="Puducherry">Puducherry</option>
                                              <option value="Punjab">Punjab </option>
                                              <option value="Rajasthan">Rajasthan</option>
                                              <option value="Sikkim">Sikkim</option>
                                              <option value="Tamil_Nadu">Tamil Nadu</option>
                                              <option value="Telangana">Telangana</option>
                                              <option value="Tripura">Tripura</option>
                                              <option value="Uttar_Pradesh">Uttar Pradesh</option>
                                              <option value="Uttarakhand">Uttarakhand</option>
                                              <option value="West_Bengal">West Bengal</option>
                                           </select>
                                           <span class="error-msg" id="CompanyState-85Ae-errMsg">Enter your state/province</span>
                                        </div>
                                     </div>
                                  </div>
                               </div>
                               <div class="msaCheckbox checkboxInput section my-15">
                                  <div>
                                     <div class="field">
                                        <label class="d-flex-y-center"><input class="" type="checkbox" id="SubscriptionAgreement-arxe" name="SubscriptionAgreement" required=""> <span style="padding-left: 15px;">I agree to the <a href="#" target="_blank">Master Subscription Agreement</a>.</span></label>
                                        <span class="error-msg" id="SubscriptionAgreement-arxe-errMsg">Please read and agree to the Master Subscription Agreement</span>
                                     </div>
                                  </div>
                               </div>
                               <div class="checkboxInput section my-15">
                                  <div class="safeharbor-wrapper hide-checkbox">
                                     <div data-legal-type="disclosure">
                                        <div class="field">
                                           <div class="small">By registering, you confirm that you agree to the storing and processing of your personal data by Salesforce as described in the&nbsp;<a href="#">Privacy Statement</a>.</div>
                                        </div>
                                     </div>
                                  </div>
                               </div>
                               <div class="form_submit_button submitButton buttonCTAItemComponent parbase">
                                  <button name="start my free trial" type="submit" class="btn btn-lg btn-theme" style="width: 100%; max-width: unset;">start my free trial</button>
                               </div>
                               <div class="form-footer-wrap"></div>
                               <div class="control-rule-prevent-submit hidden"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <? require_once("footer.php"); ?>
    <? require_once("js.php"); ?>
    
</body>

</html>