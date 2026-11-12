<!DOCTYPE html>
<html lang="{{ $locale }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1220">
    <title>{{ $locale === 'ka' ? 'ციფრული გადაწყვეტილებები ბიზნესისთვის' : 'Digital solutions for growing businesses' }}</title>
    <meta name="description" content="{{ $locale === 'ka' ? 'თანამედროვე ვებგვერდები და ბიზნეს პროცესების ავტომატიზაცია.' : 'Modern websites and practical business automation for growing companies.' }}">
    <style>
        :root{color-scheme:dark;--bg:#0b1220;--panel:#111c2e;--line:#26364d;--muted:#9aabc1;--text:#f4f7fb;--blue:#67a8ff;--cyan:#75e0d1}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:radial-gradient(ellipse at 80% 0%,#172e4d 0,transparent 34%),var(--bg);color:var(--text);font:16px/1.6 Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        a{color:inherit;text-decoration:none}.wrap{width:min(1120px,calc(100% - 40px));margin:auto}.nav{height:82px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #ffffff12}.brand{font-weight:800;letter-spacing:-.04em;font-size:19px}.brand span{color:var(--blue)}.navlinks{display:flex;align-items:center;gap:26px;color:#c5d0df;font-size:14px}.lang{border:1px solid var(--line);border-radius:999px;padding:7px 12px}.hero{padding:100px 0 82px;display:grid;grid-template-columns:1.15fr .85fr;gap:50px;align-items:center}.eyebrow{display:inline-flex;gap:9px;align-items:center;border:1px solid #4c83bd66;background:#18304c88;color:#b8d8ff;border-radius:999px;padding:7px 13px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}.dot{width:7px;height:7px;border-radius:50%;background:var(--cyan);box-shadow:0 0 14px var(--cyan)}h1{font-size:clamp(42px,6vw,68px);line-height:1.04;letter-spacing:-.055em;margin:23px 0 20px;max-width:720px}h1 em{font-style:normal;color:var(--blue)}.lead{font-size:18px;color:#b4c2d4;max-width:610px}.actions{display:flex;gap:13px;flex-wrap:wrap;margin-top:30px}.btn{display:inline-flex;align-items:center;justify-content:center;border-radius:10px;padding:13px 19px;font-weight:700;font-size:14px;border:1px solid var(--line)}.primary{background:var(--blue);color:#07111f;border-color:var(--blue)}.secondary{background:#ffffff08}.visual{border:1px solid #38506e;background:linear-gradient(145deg,#172b44,#0e192a);border-radius:22px;padding:23px;box-shadow:0 25px 80px #0005;transform:rotate(1deg)}.visual-top{display:flex;justify-content:space-between;color:#9eb0c7;font-size:12px;border-bottom:1px solid var(--line);padding-bottom:16px}.metric{padding:24px 0 18px}.metric strong{font-size:34px;letter-spacing:-.04em}.metric small{display:block;color:var(--muted);font-size:12px}.bars{display:grid;gap:12px}.bar{height:9px;border-radius:20px;background:#24364e;overflow:hidden}.bar i{display:block;height:100%;border-radius:20px;background:linear-gradient(90deg,#5d9cf3,#75e0d1)}.minirow{display:flex;justify-content:space-between;color:#c8d5e4;font-size:12px;margin-bottom:6px}.section{padding:76px 0}.section-head{max-width:650px;margin-bottom:32px}.kicker{color:var(--cyan);font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.12em}.section h2{font-size:clamp(30px,4vw,43px);line-height:1.12;letter-spacing:-.04em;margin:10px 0 13px}.sub{color:var(--muted)}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.card{background:linear-gradient(150deg,#142238,#101a2a);border:1px solid var(--line);border-radius:16px;padding:23px}.icon{width:42px;height:42px;border-radius:12px;background:#25466b;color:#a9d0ff;display:grid;place-items:center;font-size:20px}.card h3{margin:17px 0 8px;font-size:18px}.card p{color:var(--muted);font-size:14px;margin:0}.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}.step{border-top:1px solid var(--line);padding-top:18px}.num{color:var(--blue);font-weight:800;font-size:13px}.step h3{margin:8px 0}.step p{color:var(--muted);font-size:14px}.contact{display:grid;grid-template-columns:.8fr 1.2fr;gap:35px;background:linear-gradient(130deg,#142a44,#101b2c);border:1px solid #38506e;border-radius:22px;padding:34px}.formgrid{display:grid;grid-template-columns:1fr 1fr;gap:13px}.field{display:grid;gap:6px}.field.full{grid-column:1/-1}label{font-size:12px;color:#c7d4e4;font-weight:700}input,select,textarea{width:100%;border:1px solid #334760;border-radius:9px;background:#0b1626;color:var(--text);padding:12px;font:inherit;font-size:14px}textarea{min-height:105px;resize:vertical}.consent{display:flex;align-items:flex-start;gap:9px;color:var(--muted);font-size:12px}.consent input{width:auto;margin-top:4px}.success{border:1px solid #347d69;background:#10352e;color:#baf3df;padding:12px;border-radius:10px;margin-bottom:14px}.error{color:#ffb4b4;font-size:12px}.footer{border-top:1px solid #ffffff12;padding:26px 0;color:var(--muted);font-size:12px;display:flex;justify-content:space-between;gap:20px}
        @media(max-width:800px){.hero{grid-template-columns:1fr;padding:72px 0 55px;gap:35px}.visual{max-width:520px}.cards,.steps{grid-template-columns:1fr}.contact{grid-template-columns:1fr;padding:24px}.navlinks{gap:12px}.navlinks .hide{display:none}}
        @media(max-width:520px){.wrap{width:min(100% - 28px,1120px)}.nav{height:70px}.hero{padding-top:55px}h1{font-size:43px}.lead{font-size:16px}.formgrid{grid-template-columns:1fr}.field.full{grid-column:auto}.section{padding:56px 0}.footer{flex-direction:column}}
    </style>
</head>
<body>
@php
    $ka = $locale === 'ka';
    $t = $ka ? [
        'nav_services'=>'სერვისები','nav_process'=>'პროცესი','nav_contact'=>'კონტაქტი',
        'eyebrow'=>'ციფრული გადაწყვეტილებები ბიზნესისთვის',
        'title_a'=>'ნაკლები რუტინა.','title_b'=>'მეტი ზრდა.',
        'lead'=>'ვქმნით თანამედროვე ვებგვერდებსა და მარტივ ავტომატიზაციებს, რომლებიც თქვენს ბიზნესს ეხმარება მეტი მოთხოვნის მიღებასა და ყოველდღიური პროცესების გამარტივებაში.',
        'start'=>'პროექტის განხილვა','services_btn'=>'სერვისები',
        'dashboard'=>'ბიზნესის ციფრული ნაკადი','overview'=>'სისტემის მიმოხილვა','leads'=>'ახალი მოთხოვნები','automated'=>'ავტომატიზებული პროცესები','response'=>'პასუხის დრო',
        'services_k'=>'რას ვაკეთებთ','services_h'=>'საჭირო ტექნოლოგია, ზედმეტი სირთულის გარეშე','services_p'=>'ვიწყებთ იმ პრობლემით, რომელიც თქვენს გუნდს დროს ან მომხმარებელს აკარგვინებს.',
        'web_h'=>'ბიზნეს ვებგვერდები','web_p'=>'სწრაფი, მობილურზე მორგებული ვებგვერდები მკაფიო შეთავაზებითა და მომხმარებლის მოთხოვნის ფორმით.',
        'auto_h'=>'ბიზნეს პროცესების ავტომატიზაცია','auto_p'=>'ფორმების, შეტყობინებებისა და მონაცემების დაკავშირება, რათა განმეორებითი სამუშაო შემცირდეს.',
        'crm_h'=>'მოთხოვნების მართვა','crm_p'=>'მომხმარებლის მოთხოვნების ერთ სივრცეში შეგროვება, სტატუსები და შემდგომი კომუნიკაციის კონტროლი.',
        'process_k'=>'როგორ ვმუშაობთ','process_h'=>'მარტივი პროცესი, გამჭვირვალე შედეგი','step1'=>'ვარკვევთ საჭიროებას','step1p'=>'ვაზუსტებთ მიზანს, მომხმარებლის გზასა და აუცილებელ ფუნქციებს.','step2'=>'ვქმნით და ვამოწმებთ','step2p'=>'ვაწყობთ ვებგვერდსა და ავტომატიზაციას, შემდეგ ვამოწმებთ რეალურ სცენარებს.','step3'=>'ვუშვებთ და ვუვლით','step3p'=>'გადმოგცემთ სისტემას, ინსტრუქციას და საჭიროების შემთხვევაში ვაგრძელებთ მხარდაჭერას.',
        'contact_k'=>'დავიწყოთ საუბარი','contact_h'=>'მოგვიყევით თქვენი პროექტის შესახებ','contact_p'=>'მოკლედ აღწერეთ რა გჭირდებათ. დაგიკავშირდებით და ერთად განვიხილავთ შესაბამის გადაწყვეტას.',
        'name'=>'სახელი','email'=>'ელფოსტა','phone'=>'ტელეფონი','company'=>'კომპანია','service'=>'რა გჭირდებათ?','choose'=>'აირჩიეთ სერვისი','website'=>'ვებგვერდი','automation'=>'ავტომატიზაცია','bundle'=>'ვებგვერდი + ავტომატიზაცია','other'=>'სხვა','message'=>'პროექტის მოკლე აღწერა','consent'=>'ვეთანხმები ჩემი საკონტაქტო ინფორმაციის გამოყენებას ამ მოთხოვნაზე პასუხის გასაცემად.','privacy'=>'მონაცემები გამოიყენება მხოლოდ თქვენს მოთხოვნაზე საპასუხოდ. არ გამოგზავნოთ სენსიტიური ინფორმაცია.','send'=>'მოთხოვნის გაგზავნა','success'=>'მადლობა! თქვენი მოთხოვნა მიღებულია.','required'=>'შეავსეთ სავალდებულო ველები.','footer'=>'ციფრული გადაწყვეტილებები ბიზნესის ზრდისთვის.'
    ] : [
        'nav_services'=>'Services','nav_process'=>'Process','nav_contact'=>'Contact',
        'eyebrow'=>'Digital solutions for growing businesses',
        'title_a'=>'Less busywork.','title_b'=>'More growth.',
        'lead'=>'We build modern websites and practical automations that help your business capture more enquiries and simplify everyday operations.',
        'start'=>'Discuss a project','services_btn'=>'Our services',
        'dashboard'=>'Business workflow','overview'=>'System overview','leads'=>'New enquiries','automated'=>'Automated workflows','response'=>'Response time',
        'services_k'=>'What we do','services_h'=>'Useful technology, without unnecessary complexity','services_p'=>'We start with the problem that costs your team time or customers.',
        'web_h'=>'Business websites','web_p'=>'Fast, mobile-friendly websites with a clear offer and an effective enquiry form.',
        'auto_h'=>'Workflow automation','auto_p'=>'Connect forms, notifications and data to reduce repetitive manual work.',
        'crm_h'=>'Enquiry management','crm_p'=>'Keep customer enquiries in one place with clear statuses and follow-up visibility.',
        'process_k'=>'How we work','process_h'=>'A simple process. A clear result.','step1'=>'Understand the need','step1p'=>'We clarify the goal, customer journey and essential functionality.','step2'=>'Build and test','step2p'=>'We build the website and automation, then test realistic scenarios.','step3'=>'Launch and support','step3p'=>'We hand over the system, explain how it works and provide ongoing support if needed.',
        'contact_k'=>'Start a conversation','contact_h'=>'Tell us about your project','contact_p'=>'Share a few details. We will get back to you and discuss a practical solution.',
        'name'=>'Your name','email'=>'Email','phone'=>'Phone','company'=>'Company','service'=>'What do you need?','choose'=>'Choose a service','website'=>'Business website','automation'=>'Workflow automation','bundle'=>'Website + automation','other'=>'Something else','message'=>'Brief project description','consent'=>'I agree that my contact details may be used to respond to this enquiry.','privacy'=>'Your details are used only to respond to this enquiry. Please do not submit sensitive information.','send'=>'Send enquiry','success'=>'Thank you! Your enquiry has been received.','required'=>'Please check the required fields.','footer'=>'Digital solutions for business growth.'
    ];
@endphp
<header class="wrap nav">
    <a class="brand" href="{{ $ka ? '/' : '/en' }}">YOUR<span>STUDIO</span></a>
    <nav class="navlinks">
        <a class="hide" href="#services">{{ $t['nav_services'] }}</a>
        <a class="hide" href="#process">{{ $t['nav_process'] }}</a>
        <a href="#contact">{{ $t['nav_contact'] }}</a>
        <a class="lang" href="{{ $ka ? '/en' : '/' }}">{{ $ka ? 'EN' : 'ქარ' }}</a>
    </nav>
</header>
<main>
    <section class="wrap hero">
        <div>
            <div class="eyebrow"><span class="dot"></span>{{ $t['eyebrow'] }}</div>
            <h1>{{ $t['title_a'] }} <em>{{ $t['title_b'] }}</em></h1>
            <p class="lead">{{ $t['lead'] }}</p>
            <div class="actions">
                <a class="btn primary" href="#contact">{{ $t['start'] }} ↗</a>
                <a class="btn secondary" href="#services">{{ $t['services_btn'] }}</a>
            </div>
        </div>
        <div class="visual" aria-label="{{ $t['dashboard'] }}">
            <div class="visual-top"><span>◈ &nbsp; {{ $t['dashboard'] }}</span><span>● CONCEPT PREVIEW</span></div>
            <div class="metric"><small>{{ $t['overview'] }}</small><strong>01 — 04</strong></div>
            <div class="bars">
                <div><div class="minirow"><span>{{ $t['leads'] }}</span><b>INBOX</b></div><div class="bar"><i style="width:78%"></i></div></div>
                <div><div class="minirow"><span>{{ $t['automated'] }}</span><b>3 steps</b></div><div class="bar"><i style="width:62%"></i></div></div>
                <div><div class="minirow"><span>{{ $t['response'] }}</span><b>READY</b></div><div class="bar"><i style="width:88%"></i></div></div>
            </div>
        </div>
    </section>
    <section class="section" id="services">
        <div class="wrap">
            <div class="section-head"><div class="kicker">{{ $t['services_k'] }}</div><h2>{{ $t['services_h'] }}</h2><p class="sub">{{ $t['services_p'] }}</p></div>
            <div class="cards">
                <article class="card"><div class="icon">⌘</div><h3>{{ $t['web_h'] }}</h3><p>{{ $t['web_p'] }}</p></article>
                <article class="card"><div class="icon">↗</div><h3>{{ $t['auto_h'] }}</h3><p>{{ $t['auto_p'] }}</p></article>
                <article class="card"><div class="icon">▦</div><h3>{{ $t['crm_h'] }}</h3><p>{{ $t['crm_p'] }}</p></article>
            </div>
        </div>
    </section>
    <section class="section" id="process">
        <div class="wrap">
            <div class="section-head"><div class="kicker">{{ $t['process_k'] }}</div><h2>{{ $t['process_h'] }}</h2></div>
            <div class="steps">
                <article class="step"><span class="num">01 / DISCOVER</span><h3>{{ $t['step1'] }}</h3><p>{{ $t['step1p'] }}</p></article>
                <article class="step"><span class="num">02 / BUILD</span><h3>{{ $t['step2'] }}</h3><p>{{ $t['step2p'] }}</p></article>
                <article class="step"><span class="num">03 / LAUNCH</span><h3>{{ $t['step3'] }}</h3><p>{{ $t['step3p'] }}</p></article>
            </div>
        </div>
    </section>
    <section class="section" id="contact">
        <div class="wrap contact">
            <div><div class="kicker">{{ $t['contact_k'] }}</div><h2>{{ $t['contact_h'] }}</h2><p class="sub">{{ $t['contact_p'] }}</p></div>
            <div>
                @if(session('lead_success'))<div class="success" role="status">{{ $t['success'] }}</div>@endif
                @if($errors->any())<div class="error" role="alert">{{ $t['required'] }} {{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('agency.leads.store') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <div style="position:absolute;left:-10000px" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
                    <div class="formgrid">
                        <div class="field"><label for="name">{{ $t['name'] }} *</label><input id="name" name="name" required maxlength="120" value="{{ old('name') }}"></div>
                        <div class="field"><label for="company">{{ $t['company'] }}</label><input id="company" name="company" maxlength="160" value="{{ old('company') }}"></div>
                        <div class="field"><label for="email">{{ $t['email'] }}</label><input id="email" type="email" name="email" maxlength="180" value="{{ old('email') }}"></div>
                        <div class="field"><label for="phone">{{ $t['phone'] }}</label><input id="phone" name="phone" maxlength="40" value="{{ old('phone') }}"></div>
                        <div class="field full"><label for="service">{{ $t['service'] }} *</label><select id="service" name="service" required><option value="">{{ $t['choose'] }}</option><option value="website">{{ $t['website'] }}</option><option value="automation">{{ $t['automation'] }}</option><option value="bundle">{{ $t['bundle'] }}</option><option value="other">{{ $t['other'] }}</option></select></div>
                        <div class="field full"><label for="message">{{ $t['message'] }}</label><textarea id="message" name="message" maxlength="3000">{{ old('message') }}</textarea></div>
                        <div class="field full"><label class="consent"><input type="checkbox" name="consent" value="1" required><span>{{ $t['consent'] }} *</span></label></div>
                        <div class="field full"><button class="btn primary" type="submit">{{ $t['send'] }} ↗</button></div>
                    </div>
                </form>
                <p class="sub" style="font-size:11px;margin-top:10px">{{ $t['privacy'] }}</p>
            </div>
        </div>
    </section>
</main>
<footer class="wrap footer"><span>DIGITALSTUDIO © {{ date('Y') }}</span><span>{{ $t['footer'] }}</span></footer>
</body>
</html>
