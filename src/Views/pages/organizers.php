<?php $base = $base ?? ""; ?>

<section class="organizers-page">
  <section class="organizers-hero">
    <div class="container-ce organizers-hero-content">
      <h1 class="organizers-hero-title">Become part of the city event scene</h1>
      <p class="organizers-hero-lead">Reach thousands of city visitors, present information clearly, and conveniently manage tickets and attendees.</p>
      <div class="organizers-actions-inline">
        <a href="<?= $base ?>/create-event" class="btn btn-primary">Create event</a>
        <a href="<?= $base ?>/organizer-events" id="myEventsBtn" class="btn btn-outline">My Events</a>
      </div>
    </div>
  </section>

  <section class="events-section organizers-section">
    <div class="container-ce">
      <div class="section-header organizers-section-header">
        <div>
          <h2 class="organizers-section-title">How to get started?</h2>
          <p class="organizers-section-lead">Three simple steps to your event's success.</p>
        </div>
      </div>
      <div class="events-grid">
        <article class="step-card">
          <div class="step-number">1</div>
          <h3 class="event-title">Create your profile</h3>
          <p>Sign in to CityEvents and complete your organizer information. This helps visitors recognize your brand.</p>
        </article>

        <article class="step-card">
          <div class="step-number">2</div>
          <h3 class="event-title">Register your event</h3>
          <p>Enter a title, category, date, and location. Add an appealing image to stand out on the map.</p>
        </article>

        <article class="step-card">
          <div class="step-number">3</div>
          <h3 class="event-title">Publish and monitor</h3>
          <p>Once approved by an administrator, your event becomes visible to everyone. Track views and update information in real time.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="events-section organizers-section organizers-section-surface">
    <div class="container-ce">
      <div class="section-header organizers-section-header">
        <div>
          <h2 class="organizers-section-title">Why choose CityEvents?</h2>
          <p class="organizers-section-lead">Tools built to grow your audience.</p>
        </div>
      </div>
      <div class="events-grid">
        <article class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
          </div>
          <h3 class="event-title">Interactive map</h3>
          <p>Visitors see events based on their location. A great way to attract spontaneous attendees.</p>
        </article>

        <article class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </div>
          <h3 class="event-title">Easy discoverability</h3>
          <p>Categories and smart filters help the right audience find your event.</p>
        </article>

        <article class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          </div>
          <h3 class="event-title">Build loyalty</h3>
          <p>The "Favorites" feature lets visitors save your event and helps you stay top-of-mind before it starts.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="events-section organizers-section organizers-section-mt">
    <div class="container-ce">
      <div class="section-header organizers-section-header">
        <h2 class="organizers-section-title">Quick actions</h2>
      </div>
      <div class="organizers-quick-grid">
        <article class="organizers-quick-card organizers-quick-card-primary">
          <h3 class="event-title">Create a new event</h3>
          <p>Fill out the form and get started.</p>
          <a href="<?= $base ?>/create-event" class="btn organizers-quick-btn">Start</a>
        </article>

        <article class="organizers-quick-card organizers-quick-card-dark">
          <h3 class="event-title">My Events</h3>
          <p>Manage information for your existing events.</p>
          <a href="<?= $base ?>/organizer-events" class="btn organizers-quick-btn-outline">View</a>
        </article>
      </div>
    </div>
  </section>
</section>
