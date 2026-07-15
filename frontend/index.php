

<?php
require_once __DIR__ . '/../Backend/database.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nepal Tour and Travel - Discover the Magic of Nepal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=4">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="../img/logo.png?v=2" alt="Logo" class="logo-icon">
                <span class="logo-text">
                    Nepal
                    <span class="logo-subtitle">Tour & Travel</span>
                </span>
            </a>
            <ul class="nav-menu">
                <li><a href="#places" class="nav-link">Places</a></li>
                <li><a href="#things" class="nav-link">Activities</a></li>
                <li><a href="#festivals" class="nav-link">Festivals</a></li>
                <li><a href="#plan" class="nav-link">Plan</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="admin.php" class="nav-link admin-nav-link">Admin</a></li>
                <?php if ($user): ?>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="signup.php" class="nav-link">Signup</a></li>
                <?php endif; ?>
                
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="nepali-text-container">
            <div class="nepali-text">NEPAL</div>
        </div>
        <div class="hero-content">
            <div class="hero-content">

    <div class="hero-badge">
      <h2><b> ⭐⭐⭐⭐⭐ Trusted by 5,000+ Travelers</b></h2>
    </div>

    <h1>Discover the Magic of Nepal</h1>

    <p>
        Explore breathtaking mountains, ancient temples,
        vibrant festivals and unforgettable adventures
        across the Himalayas.
    </p>

    <div class="hero-buttons">
        <a href="#places" class="cta-button">
            Explore Destinations
        </a>

        <a href="#plan" class="cta-button cta-button-secondary">
            Plan Your Trip
        </a>
    </div>

    <div class="hero-features">
        <span>✔ Local Guides</span>
        <span>✔ Best Price</span>
        <span>✔ Safe Travel</span>
    </div>

</div>
        </div>
    </section>
    <!-- Latest Stories Section -->
    <section class="latest-stories" id="stories">
        <div class="container">
            <h2 class="section-title">Latest Stories</h2>
          <b>  <p class="section-subtitle">Discover inspiring travel stories and experiences from our community</p></b>
            <div class="stories-grid">
                <article class="story-card">
                    <div class="story-image-wrapper">
                        <img src="img/3.jpeg" alt="Story 1">
                        <span class="story-badge">Featured</span>
                    </div>
                    <div class="story-content">
                        <span class="story-date">May 15, 2026</span>
                        <h3>Trekking in the Himalayas</h3>
                        <p>Discover the best trekking routes and prepare for your mountain adventure with expert tips.</p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </article>
                <article class="story-card">
                    <div class="story-image-wrapper">
                        <img src="img/4.jpeg" alt="Story 2">
                        <span class="story-badge">Popular</span>
                    </div>
                    <div class="story-content">
                        <span class="story-date">May 12, 2026</span>
                        <h3>Cultural Heritage Sites</h3>
                        <p>Explore the ancient temples and cultural landmarks that define Nepal's rich history.</p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </article>
                <article class="story-card">
                    <div class="story-image-wrapper">
                        <img src="img/5.jpeg" alt="Story 3">
                        <span class="story-badge">Trending</span>
                    </div>
                    <div class="story-content">
                        <span class="story-date">May 10, 2026</span>
                        <h3>Adventure Activities</h3>
                     <p>From paragliding to white-water rafting, find your next adrenaline-pumping experience.</p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </article>
            </div>
            <button class="view-all-btn">View All Stories</button>
        </div>
    </section>

    <!-- Places to Go Section -->
    <section class="places" id="places">
        <div class="container">
            <h2 class="section-title">Places to Go</h2>
            <p class="section-subtitle"><B>Explore the most stunning destinations across Nepal</b></p>
            
            <!-- Category Filter Buttons -->
            <div class="places-categories">
                <button class="category-btn active" data-category="provinces">Provinces</button>
                <button class="category-btn" data-category="heritage">World Heritage (UNESCO)</button>
                <button class="category-btn" data-category="protected">Protected Area</button>
                <button class="category-btn" data-category="cities">Cities and Towns</button>
                <button class="category-btn" data-category="peaks">Eight Thousanders</button>
                <button class="category-btn" data-category="pilgrimage">Pilgrimage Sites</button>
                <button class="category-btn" data-category="hills">Mid Hills</button>
            </div>
            
            <!-- Places Grid -->
            <div class="places-grid" id="places-grid">
                <!-- Provinces Category -->
             
                     <div class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/38/7a/21/387a21d7763974798937d09cecf7418f.jpg" alt="Koshi">
                        <div class="place-overlay">
                            <h3>Koshi</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://chinarinepal.com/wp-content/uploads/2022/03/JANAKI-MANDIR-1024x386.png" alt="Madhesh">
                        <div class="place-overlay">
                            <h3>Madhesh</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/1200x/be/a3/e5/bea3e57e5abd2abb4d3ada67e7c200dc.jpg" alt="Bagmati">
                        <div class="place-overlay">
                            <h3>Bagmati</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/56/3e/91/563e9142137cad48487a45b9bba77d62.jpg" alt="Gandaki">
                        <div class="place-overlay">
                            <h3>Gandaki</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/1200x/b3/83/c2/b383c22c9aa6854d763a9cbbbe1daed9.jpg" alt="Lumbini">
                        <div class="place-overlay">
                            <h3>Lumbini</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/4e/ba/4a/4eba4a5546a3525b36808a0f35aecf15.jpg" alt="Karnali">
                        <div class="place-overlay">
                            <h3>Karnali</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/f2/8f/12/f28f121913883f59a3fa9e466f56ee7e.jpg" alt="Sudurpashchim">
                        <div class="place-overlay">
                            <h3>Sudurpashchim</h3>
                        </div>
                    </div>
                </div>
                        <!-- Heritage Category -->
                <div class="place-card" data-category="heritage" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/1200x/23/cc/01/23cc014972450266ecd4c8493139422a.jpg" alt="Kathmandu Durbar Square">
                        <div class="place-overlay">
                            <h3>Kathmandu Durbar Square</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="heritage" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/6c/e3/6f/6ce36f494ed1bde35a2585e9a089df90.jpg" alt="Bhaktapur">
                        <div class="place-overlay">
                            <h3>Bhaktapur Durbar Square</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="heritage" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/1b/16/e9/1b16e9bb549a9776e4480e81dfa193c4.jpg" alt="Patan">
                        <div class="place-overlay">
                            <h3>Patan Durbar Square</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="heritage" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/e8/75/6e/e8756e1693c9e2e55738c2fcf464e9b7.jpg" alt="Lumbini">
                        <div class="place-overlay">
                            <h3>Lumbini - Birthplace of Buddha</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="heritage" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/1200x/06/41/58/0641580b200efd746e90e452b9f6e6e6.jpg" alt="Boudhanath Stupa">
                        <div class="place-overlay">
                            <h3>Boudhanath Stupa</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="heritage" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/1200x/28/06/72/280672c862ce34058e1bd00346c0a847.jpg" alt="Pashupatinath Temple">
                        <div class="place-overlay">
                            <h3>Pashupatinath Temple</h3>
                        </div>
                    </div>
                </div>
                
                <!-- Protected Area Category -->
                <div class="place-card" data-category="protected" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/54/18/d0/5418d03d29c9e31370b567ce516584c1.jpg" alt="Chitwan National Park">
                        <div class="place-overlay">
                            <h3>Chitwan National Park</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="protected" style="display:none;">
                    <div class="place-image">
                        <img src="https://media.tacdn.com/media/attractions-splice-spp-674x446/0b/39/79/12.jpg" alt="Sagarmatha National Park">
                        <div class="place-overlay">
                            <h3>Sagarmatha National Park</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="protected" style="display:none;">
                    <div class="place-image">
                        <img src="https://tse3.mm.bing.net/th/id/OIP.Uy8cOh8QNxRq8hV2o3kMtAHaEo?r=0&rs=1&pid=ImgDetMain&o=7&rm=3" alt="Langtang National Park">
                        <div class="place-overlay">
                            <h3>Langtang National Park</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="protected" style="display:none;">
                    <div class="place-image">
                        <img src="https://www.himalayanforever.com/public/storage/trip-seos/19/5db49d8d1d42d2db790bcce43379e5d6.jpg" alt="Rara Lake">
                        <div class="place-overlay">
                            <h3>Rara Lake National Park</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="protected" style="display:none;">
                    <div class="place-image">
                        <img src="https://cdn.kimkim.com/files/a/images/0154e137ed055ff13ba90694407f33515ff781ba/original-c35f44388a4f983b7c6330d833878f61.jpg" alt="Bardia National Park">
                        <div class="place-overlay">
                            <h3>Bardia National Park</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="protected" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/6c/39/0b/6c390b2c65c90675ed54577b3def33a3.jpg" alt="Shey Phoksundo Lake">
                        <div class="place-overlay">
                            <h3>Shey Phoksundo Lake</h3>
                        </div>
                    </div>
                </div>
                
                <!-- Cities and Towns Category -->
                <div class="place-card" data-category="cities" style="display:none;">
                    <div class="place-image">
                        <img src="img/8.jpeg" alt="Kathmandu">
                        <div class="place-overlay">
                            <h3>Kathmandu</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="cities" style="display:none;">
                    <div class="place-image">
                        <img src="https://wallpaperaccess.com/full/4401237.jpg" alt="Pokhara">
                        <div class="place-overlay">
                            <h3>Pokhara</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="cities" style="display:none;">
                    <div class="place-image">
                        <img src="https://c8.alamy.com/comp/C3A701/durbar-square-of-patan-lalitpur-in-kathmandu-nepal-C3A701.jpg" alt="Lalitpur">
                        <div class="place-overlay">
                            <h3>Lalitpur (Patan)</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="cities" style="display:none;">
                    <div class="place-image">
                        <img src="https://static.toiimg.com/photo/54311463/.jpg" alt="Bhaktapur">
                        <div class="place-overlay">
                            <h3>Bhaktapur</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="cities" style="display:none;">
                    <div class="place-image">
                        <img src="https://komalhotels.com/storage/janaki-mandir-of-janakpurdham-nepal.jpg" alt="Janakpur">
                        <div class="place-overlay">
                            <h3>Janakpur</h3>
                        </div>
                    </div>
                </div>
            
                    </div>
                </div>
                <div class="place-card" data-category="cities" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/f4/5d/56/f45d56303612b3a80d8dff73270bdd17.jpg" alt="Dharan">
                        <div class="place-overlay">
                            <h3>Dharan</h3>
                        </div>
                    </div>
                </div>
                
                <!-- Eight Thousanders Category -->
                <div class="place-card" data-category="peaks" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/15/d3/80/15d380f1482a012bf6a9c67efefcd28d.jpg" alt="Mount Everest">
                        <div class="place-overlay">
                            <h3>Mount Everest (8,849m)</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="peaks" style="display:none;">
                    <div class="place-image">
                        <img src="https://th.bing.com/th/id/R.f30e8de98f4df71194c14ff08fe03168?rik=7aXgZC4YmM%2bSlg&pid=ImgRaw&r=0" alt="Kangchenjunga">
                        <div class="place-overlay">
                            <h3>Kangchenjunga (8,586m)</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="peaks" style="display:none;">
                    <div class="place-image">
                        <img src="https://climbing4sdgs.com/wp-content/uploads/elementor/thumbs/Mt.-Lhotse-q3oobij347nqlzcvzp1c1uibci6ai16yx433vltn4o.jpg" alt="Lhotse">
                        <div class="place-overlay">
                            <h3>Lhotse (8,516m)</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="peaks" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/01/99/65/0199659033e094c0c576f5f76f600135.jpg" alt="Makalu">
                        <div class="place-overlay">
                            <h3>Makalu (8,485m)</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="peaks" style="display:none;">
                    <div class="place-image">
                        <img src="https://live.staticflickr.com/65535/54398125656_91afcbae53_b.jpg" alt="Cho Oyu">
                        <div class="place-overlay">
                            <h3>Cho Oyu (8,188m)</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="peaks" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/10/29/a3/1029a3d7fc29f45b6acd4f305e1b0575.jpg" alt="Dhaulagiri">
                        <div class="place-overlay">
                            <h3>Dhaulagiri I (8,167m)</h3>
                        </div>
                    </div>
                </div>
                
                <!-- Pilgrimage Sites Category -->
                <div class="place-card" data-category="pilgrimage" style="display:none;">
                    <div class="place-image">
                        <img src="https://www.purevacations.com/wp-content/uploads/2023/05/World-Peace-Stupa-in-Lumbini-1024x683.jpg" alt="Lumbini">
                        <div class="place-overlay">
                            <h3>Lumbini</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="pilgrimage" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/originals/10/3a/27/103a27cbdb069603fd534d33ddbabe23.jpg" alt="Pashupatinath">
                        <div class="place-overlay">
                            <h3>Pashupatinath Temple</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="pilgrimage" style="display:none;">
                    <div class="place-image">
                        <img src="https://www.wondersofnepal.com/wp-content/uploads/2019/10/bodhnath-stupa-1024x683.jpg" alt="Boudhanath">
                        <div class="place-overlay">
                            <h3>Boudhanath Stupa</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="pilgrimage" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/1200x/cd/39/91/cd39917ce4d99c4628729eaab25e1a76.jpg" alt="Janakpur">
                        <div class="place-overlay">
                            <h3>Janakpur (Mithila)</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="pilgrimage" style="display:none;">
                    <div class="place-image">
                        <img src="https://1.bp.blogspot.com/-FfOe5crZXAQ/XaFn98m-CTI/AAAAAAAAAMA/HxXdM519zxcVgF8En-st2wgpvoe1SSnQwCLcBGAsYHQ/s1600/muktinath-darshan-yatra86.jpg" alt="Muktinath">
                        <div class="place-overlay">
                            <h3>Muktinath Temple</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="pilgrimage" style="display:none;">
                    <div class="place-image">
                        <img src="https://media-cdn.tripadvisor.com/media/photo-m/1280/1b/9e/9b/0c/namo-buddha-the-name.jpg" alt="Namo Buddha">
                        <div class="place-overlay">
                            <h3>Namo Buddha</h3>
                        </div>
                    </div>
                </div>
                
                <!-- Mid Hills Category -->
                <div class="place-card" data-category="hills" style="display:none;">
                    <div class="place-image">
                        <img src="https://travel80.com/wp-content/uploads/2025/08/2025-08-22-002-Nagarkot-sunrise-view.png" alt="Nagarkot">
                        <div class="place-overlay">
                            <h3>Nagarkot</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="hills" style="display:none;">
                    <div class="place-image">
                        <img src="https://www.holidify.com/images/cmsuploads/compressed/town_20181010191058_20181010191109.jpg" alt="Dhulikhel">
                        <div class="place-overlay">
                            <h3>Dhulikhel</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="hills" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/6c/6f/4d/6c6f4dcc0f9240ef799b002d1ca6e341.jpg" alt="Bandipur">
                        <div class="place-overlay">
                            <h3>Bandipur</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="hills" style="display:none;">
                    <div class="place-image">
                        <img src="https://www.nepalvisitinfo.com/wp-content/uploads/2019/09/gorkha-durbar.png" alt="Gorkha">
                        <div class="place-overlay">
                            <h3>Gorkha</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="hills" style="display:none;">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/1200x/ba/ba/17/baba174ce450ab066d19a1b277f5da22.jpg" alt="Ilam">
                        <div class="place-overlay">
                            <h3>Ilam (Tea Gardens)</h3>
                        </div>
                    </div>
                </div>
                <div class="place-card" data-category="hills" style="display:none;">
                    <div class="place-image">
                        <img src="https://3.bp.blogspot.com/-5b1FHZDnYEc/Wf8bGMXtUdI/AAAAAAAAFmw/boGOZgY-YEYc9kNFkiWFW_X6GoLDy7HGwCLcBGAs/s1600/Nuwakot%2Bpalace.png" alt="Nuwakot">
                        <div class="place-overlay">
                            <h3>Nuwakot</h3>
                        </div>
                    </div>
                </div>
            </div>
                        <button class="view-all-btn">View All</button>
        </div>
    </section>
    <!-- Things to Do Section -->
    <section class="things-to-do" id="things">
        <div class="container">
            <h2 class="section-title">Things to Do</h2>
            <p class="section-subtitle"><b>Endless activities and experiences for every type of traveler</b></p>
             <div class="activities-grid"><a href="trekking.php" > 
                <div class="activity-card">
                    <div class="activity-icon">🥾</div>
               <h3>Trekking</h3>
                    <p>Explore scenic trails through mountains and valleys with breathtaking views.</p>
                </div></a>
                <a href="yoga.php" >
                <div class="activity-card">
                    <div class="activity-icon">🧘</div>
                    <h3>Meditation & Yoga</h3>
                    <p>Find inner peace in spiritual retreats and ashrams across the country.</p>
                </div></a>
                 <a href="paragliding.php" >
                <div class="activity-card">
                    <div class="activity-icon">🪂</div>
                    <h3>Paragliding</h3>
                    <p>Experience the thrill of flying over beautiful landscapes and mountain peaks.</p>
                </div></a>
                 <a href="photography.php" >
                <div class="activity-card">
                    <div class="activity-icon">📸</div>
                    <h3>Photography</h3>
                    <p>Capture stunning moments in nature and culture with professional guidance.</p>
                </div></a>
                 <a href="culinary.php" >
                <div class="activity-card">
                    <div class="activity-icon">🍽️</div>
                    <h3>Culinary Tours</h3>
                    <p>Taste authentic Nepali cuisine and local delicacies in traditional settings.</p>
                </div></a>
            </div>
        </div>
    </section>

  

    <!-- Plan Your Trip Section -->
    <section class="plan-trip" id="plan">
        <div class="container">
            <h2 class="section-title">Plan Your Trip</h2>
            <p class="section-subtitle"><B>Simple steps to organize your perfect Nepal adventure</B></p>
            <div class="trip-planning">
                <div class="planning-step">
                    <div class="step-number">1</div>
                    <h3>Choose Your Destination</h3>
                    <p>Browse through our curated list of destinations and attractions that match your interests.</p>
                </div>
                <div class="planning-step">
                    <div class="step-number">2</div>
                    <h3>Select Duration</h3>
                    <p>Plan your trip based on available time and the experiences you want to have.</p>
                </div>
                <div class="planning-step">
                    <div class="step-number">3</div>
                    <h3>Book Accommodations</h3>
                    <p>Find and reserve hotels, resorts, and lodges that suit your budget and preferences.</p>
                </div>
                <div class="planning-step">
                    <div class="step-number">4</div>
                    <h3>Arrange Transport</h3>
                    <p>Book flights, buses, or hire local guides and vehicles for seamless travel.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <a href="index.php" class="footer-brand">
                    <img src="../img/logo.png?v=3" alt="Nepal Tour & Travel Logo">
                    <div>
                        <h3>Nepal Tour & Travel</h3>
                        <span>Discover Nepal with comfort and confidence</span>
                    </div>
                </a>

                <p class="footer-description">
                    Curated tours, mountain adventures, cultural escapes, and trusted local guidance for an unforgettable Nepal experience.
                </p>

                <div class="footer-badges" aria-label="Highlights">
                    <span>24/7 Support</span>
                    <span>Local Experts</span>
                    <span>Trusted Guides</span>
                </div>
            </div>

            <div class="footer-content">
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#places">Places</a></li>
                        <li><a href="index.php#things">Activities</a></li>
                        <li><a href="about.php">About Us</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Follow Us</h4>
                    <p class="footer-copy">
                        Stay connected for travel inspiration, updates, and destination highlights.
                    </p>
                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-envelope"></i> info@nepalitourtravel.com</p>
                    <p><i class="fas fa-phone"></i> +977 9763658085</p>
                    <p><i class="fas fa-map-marker-alt"></i> Butwal, Nepal</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Nepal Tour and Travel. All rights reserved.</p>
                <p><a href="#">Privacy Policy</a> <span>•</span> <a href="#">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <button id="scrollToTop" class="scroll-to-top" style="display:none;"><i class="fa-solid fa-chevron-up"></i></button>
    <script src="script.js"></script>
</body>
</html>
