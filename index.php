<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set cache control headers to prevent caching after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>ImpactPass - Event Management</title>
    <link rel="stylesheet" href="./css/index.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="manifest" href="manifest.json">
    <script src="js/sw-register.js"></script>
    <?php include 'session-checker.php'; ?>
</head>
<body>
    <!-- Remove the duplicate include -->
    <?php include 'nav.php'; ?>
    <section class="h-full ">

    </section>
    <div class="bg-gray-50 py-2 px-4 sm:px-6 lg:px-8">
      <!-- Hero Content -->
      <div class="mx-auto flex flex-col-reverse lg:flex-row items-center justify-between max-w-7xl px-6 lg:px-8">

        <!-- Left content -->
        <div class="flex flex-col items-center sm:items-center lg:items-start text-center sm:text-center lg:text-left max-w-2xl">
          <h2 class="text-indigo-600 text-base font-semibold">
            🎟️ Book. Manage. Attend.
          </h2>

          <p class="mt-2 max-w-[600px] text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl">
            Hassle-Free <span class="text-indigo-600">Event Management</span> Starts Here
          </p>

          <div class="mt-8 flex flex-col sm:flex-row items-center sm:justify-center lg:justify-start gap-4 w-full">
            <div
              class="bg-indigo-600 p-3 sm:w-auto w-full rounded-lg text-white cursor-pointer text-center"
              "
            >
              View events
            </div>
            <div class="p-3 sm:w-auto w-full rounded-lg border-2 cursor-pointer text-center">
              Learn More
            </div>
          </div>
        </div>

        <!-- Hero Image -->
        <div class="flex-1 w-full max-w-xl mt-8 mb-0">
          <img
            src="./assets/hero.png"
            alt="Hackathon illustration"
            class="w-full h-auto object-contain"
          />
        </div>
      </div>

      <!-- Logo Strip -->
    <div class="mt-2 w-full lg:mt-1 rounded-xl  shadow-indigo-500/40 py-6 px-4 overflow-hidden">
        <div class="scroll-track">
            <div class="scroll-content">
                <!-- first set -->
                <div class="logo-set">
                    <div class="logo-item flex-shrink-0"><img src="https://app.mulearn.org/assets/%C2%B5Learn-qsNKBi56.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://ajce.acm.org/assets/LOGO23-2Riq8ZYW.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://icfoss.in/vendor/front/images/logo.svg" alt="logo" class="logo-img" /></div>
                                    <div class="logo-item flex-shrink-0"><img src="https://ajce.acm.org/assets/LOGO23-2Riq8ZYW.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://icfoss.in/vendor/front/images/logo.svg" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://app.mulearn.org/assets/%C2%B5Learn-qsNKBi56.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://ajce.acm.org/assets/LOGO23-2Riq8ZYW.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://icfoss.in/vendor/front/images/logo.svg" alt="logo" class="logo-img" /></div>
                </div>

                <!-- duplicated set for seamless loop -->
                   <div class="logo-set">
                    
                    <div class="logo-item flex-shrink-0"><img src="https://icfoss.in/vendor/front/images/logo.svg" alt="logo" class="logo-img" /></div>
                                    <div class="logo-item flex-shrink-0"><img src="https://ajce.acm.org/assets/LOGO23-2Riq8ZYW.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://icfoss.in/vendor/front/images/logo.svg" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://app.mulearn.org/assets/%C2%B5Learn-qsNKBi56.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://ajce.acm.org/assets/LOGO23-2Riq8ZYW.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://icfoss.in/vendor/front/images/logo.svg" alt="logo" class="logo-img" /></div>

                    <div class="logo-item flex-shrink-0"><img src="https://ajce.acm.org/assets/LOGO23-2Riq8ZYW.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://icfoss.in/vendor/front/images/logo.svg" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://app.mulearn.org/assets/%C2%B5Learn-qsNKBi56.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://ajce.acm.org/assets/LOGO23-2Riq8ZYW.png" alt="logo" class="logo-img" /></div>
                    <div class="logo-item flex-shrink-0"><img src="https://icfoss.in/vendor/front/images/logo.svg" alt="logo" class="logo-img" /></div>

                    
                </div>
            </div>
        </div>

        <style>
            /* container hides overflow */
            .scroll-track { overflow: hidden; }

            /* the content is twice as wide (two sets) and scrolls left by 50% */
            .scroll-content {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                gap: 100px; /* NO gap between the two sets to avoid start/end gap */
                animation: scroll 25s linear infinite;
                will-change: transform;
            }

            /* each set takes half of the scroll-content width */
            .logo-set {
                display: flex;
                align-items: center;
                /* each set occupies half the visible scroll-content so translateX(-50%) lines up exactly */
                /* flex: 0 0 50%; */
                flex: 0 0 50%;
            }

            /* control spacing between logos inside a set only */
            .logo-set .logo-item {
                margin-right: 2.5rem;
                flex: 0 0 auto;
            }

            /* remove trailing margin to ensure no extra gap between sets */
            .logo-set .logo-item:last-child {
                margin-right: 0;
            }

            /* image sizing */
            .logo-img {
                height: 1.5rem; /* 24px */
                max-height: 2rem; /* 32px on larger screens if you want */
                width: auto;
                display: block;
            }

            @keyframes scroll {
                from { transform: translateX(0); }
                to   { transform: translateX(-50%); }
            }

            /* pause on hover */
            .scroll-track:hover .scroll-content {
                animation-play-state: paused;
            }

            /* responsive sizes */
            @media (min-width: 640px) {
                .logo-img { height: 2rem; } /* 32px on sm+ */
            }
        </style>
    </div>

      <!-- Search Bar -->


        </section>



        <section>
            <section class="max-w-7xl mx-auto py-12 px-6">
                <div class="grid gap-8 lg:grid-cols-3 items-start">

                    <!-- Left column: two stacked cards -->
                    <div class="space-y-6">
                        <div class="bg-gray-100 rounded-2xl p-6 flex gap-4 items-start">
                            <div class="w-14 h-14 bg-white rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                                <!-- small illustration / icon -->
                                <svg class="w-8 h-8 text-green-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
                                    <path d="M12 3v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 7v6a4 4 0 0 0 8 0V7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M5 21h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-700">From corporate summits to music festivals, MakeMyStage scales to your show.</p>
                                <a href="#" class="inline-flex items-center mt-3 text-sm font-medium text-indigo-600 hover:underline">
                                    Check it out
                                    <span class="ml-2 inline-block transform translate-y-[1px]">→</span>
                                </a>
                            </div>
                        </div>

                        <div class="bg-gray-100 rounded-2xl p-8">
                            <h3 class="text-2xl font-semibold text-gray-900">No More Chaos</h3>
                            <p class="mt-3 text-gray-600">One place for all stage cues, slides, and timing — say goodbye to backstage confusion.</p>
                            <button class="mt-6 inline-flex items-center gap-3 bg-black text-white rounded-full px-4 py-2 shadow">
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-green-400 rounded-full">→</span>
                                Host With Us Now
                            </button>
                        </div>
                    </div>

                    <!-- Center: prominent green card -->
                    <div class="relative bg-emerald-500 rounded-3xl p-10 text-white overflow-hidden">
                        <h2 class="text-3xl font-extrabold">Make My Stage</h2>
                        <p class="mt-4 text-lg leading-relaxed">MakeMyStage gives you complete control over speakers, sessions, and stage flow. No more frantic cue cards or missed timings — everything runs like clockwork.</p>
                        <p class="mt-4 text-lg">You can see what’s happening live, fix delays instantly, and keep the audience engaged without ever breaking a sweat.</p>

                        <!-- decorative concentric rings (pure CSS shapes) -->
                        <div class="absolute -right-10 -bottom-10 w-56 h-56 rounded-full border-8 border-white opacity-30"></div>
                        <div class="absolute -right-4 -bottom-4 w-28 h-28 rounded-full border-8 border-white opacity-40"></div>

                        <!-- action pill overlapping bottom -->
                        <div class="absolute left-6 -bottom-6">
                            <a href="#" class="inline-flex items-center gap-3 bg-black text-white rounded-full px-5 py-3 shadow-lg">
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-green-400 rounded-full">→</span>
                                Check out MakeMyStage
                            </a>
                        </div>
                        <div style="height:48px"></div> <!-- spacer so content doesn't overlap the pill -->
                    </div>

                    <!-- Right: dark card with image -->
                    <div class="bg-gray-900 text-white rounded-3xl p-8">
                        <h3 class="text-2xl font-semibold">From Plan to Showtime</h3>
                        <p class="mt-3 text-gray-300">Plan your agenda, assign crew roles, and sync with AV teams — all from one dashboard. Whether it’s a keynote or a 20‑speaker lineup, MakeMyStage makes sure every moment hits on cue.</p>

                        <div class="mt-6 rounded-xl overflow-hidden bg-gray-800">
                            <img src="./assets/stage-schedule.png" alt="" class="w-full h-48 object-cover">
                        </div>
                    </div>

                </div>
            </section>
        </section>

        <!-- Add this script only if your nav.php doesn't include it properly -->

</body>
</html>