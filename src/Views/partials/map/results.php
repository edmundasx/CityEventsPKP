<section id="resultsWrap" class="map-results">
    <p class="map-results-meta">Rasta <span id="resultCount"><?= isset($events)
        ? count($events)
        : 0 ?></span> events</p>

    <?php if (!empty($events)): ?>
        <div class="map-results-list">
            <?php foreach ($events as $event): ?>
                <?php
                $category = (string) ($event["category"] ?? "");
                $district = (string) ($event["district"] ?? "");
                $date = (string) ($event["event_date"] ?? ($event["date"] ?? ""));
                $isFree = (bool) ($event["is_free"] ?? false);
                $priceType = $isFree ? "free" : "paid";
                ?>
                <article
                    class="map-result-card"
                    data-event-card="1"
                    data-event-id="<?= htmlspecialchars((string) ($event["id"] ?? "")) ?>"
                    data-category="<?= htmlspecialchars($category) ?>"
                    data-district="<?= htmlspecialchars($district) ?>"
                    data-price="<?= htmlspecialchars($priceType) ?>"
                    data-date="<?= htmlspecialchars($date) ?>"
                >
                    <h3 class="map-result-title">
                        <?= htmlspecialchars($event["title"] ?? "Event") ?>
                    </h3>
                    <p class="map-result-location">
                        <?= htmlspecialchars($event["location"] ?? "Location") ?>
                    </p>
                    <p class="map-result-date">
                        <?= htmlspecialchars(
                            ($event["date"] ?? ($event["event_date"] ?? "")) .
                                " " .
                                ($event["time"] ??
                                    ($event["event_time"] ?? "")),
                        ) ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
        <div id="resultsEmpty" class="map-results-empty" hidden>
            No events match the selected filters.
        </div>
    <?php else: ?>
        <div class="map-results-empty">
            Šiuo metu events nėra.
        </div>
    <?php endif; ?>
</section>
