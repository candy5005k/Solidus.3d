<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Contact Us - Solidus 3D Modeling";
require_once __DIR__ . "/includes/header.php";
?>

<section id="contact" aria-labelledby="contact-heading">
    <div class="sw">
        <div class="coi">
            <div class="col rl">
                <div class="sk" style="color:var(--blue2)">Get in Touch</div>
                <h2 id="contact-heading">Let's Build<br>Something<br><em>Together.</em></h2>
                <p>Our team responds within 24 hours with a detailed quote, timeline, and DFM feedback. No commitment needed. Serving B2B clients worldwide.</p>
                <div class="cdt"><span class="cdl">Email</span><span class="cdv"><a href="mailto:<?= h(SITE_EMAIL); ?>"><?= h(SITE_EMAIL); ?></a></span></div>
                <div class="cdt"><span class="cdl">Phone</span><span class="cdv"><a href="tel:<?= h(SITE_PHONE_LINK); ?>"><?= h(SITE_PHONE); ?></a></span></div>
                <div class="cdt"><span class="cdl">Address</span><span class="cdv"><?= h(SITE_LOCATION); ?></span></div>
                <div class="cdt"><span class="cdl">Follow</span><span class="cdv"><a href="#" target="_blank" rel="noopener">Facebook</a>&nbsp;&middot;&nbsp;<a href="#" target="_blank" rel="noopener">Instagram</a>&nbsp;&middot;&nbsp;<a href="#" target="_blank" rel="noopener">LinkedIn</a></span></div>
            </div>
            <form class="cf rr" id="cf" method="post" action="<?= h(site_url('api/contact.php')); ?>" novalidate>
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()); ?>">
                <input type="hidden" name="redirect" value="<?= h(site_url('contact.php')); ?>">
                <div class="hp-field" style="display:none;">
                    <label for="website">Website</label>
                    <input id="website" name="website" type="text" autocomplete="off">
                </div>
                <div class="cfr">
                    <div class="cff2"><label for="fn">First Name *</label><input type="text" id="fn" name="first_name" placeholder="John" required autocomplete="given-name"></div>
                    <div class="cff2"><label for="ln">Last Name</label><input type="text" id="ln" name="last_name" placeholder="Doe" autocomplete="family-name"></div>
                </div>
                <div class="cfr">
                    <div class="cff2"><label for="em">Email *</label><input type="email" id="em" name="email" placeholder="john@company.com" required autocomplete="email"></div>
                    <div class="cff2"><label for="ph">Phone / WhatsApp</label><input type="tel" id="ph" name="phone" placeholder="+1 800 000 0000"></div>
                </div>
                <div class="cfr">
                    <div class="cff2"><label for="co">Company</label><input type="text" id="co" name="company" placeholder="Acme Manufacturing Ltd."></div>
                    <div class="cff2"><label for="cy">Country</label><input type="text" id="cy" name="country" placeholder="United States"></div>
                </div>
                <div class="cfr cff">
                    <div class="cff2">
                        <label for="sv">Service Required *</label>
                        <select id="sv" name="service" required>
                            <option value="">Choose a service&hellip;</option>
                            <?php foreach (site_services() as $service): ?>
                                <option value="<?= h($service['title']); ?>"><?= h($service['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="cfr cff">
                    <div class="cff2">
                        <label for="dt">Project Details</label>
                        <textarea id="dt" name="details" placeholder="Describe your project &mdash; dimensions, materials, end-use, quantity, deadline&hellip;"></textarea>
                    </div>
                </div>
                <div class="cfs">
                    <span class="cfn"><svg viewBox="0 0 64 64" fill="none" width="14" height="14">
                <path d="M32 4L8 14V32C8 45.3 18.7 57.6 32 60C45.3 57.6 56 45.3 56 32V14L32 4Z" stroke="rgba(37,99,235,.6)" stroke-width="2" fill="none" />
                <path d="M21 32l8 8 14-14" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
              </svg> NDA available on request. Data never shared.</span>
                    <button type="submit" class="sbtn" id="sbtn">Send Request &#8594;</button>
                </div>
                <div id="fmsg"></div>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
