<aside class="map-filters">
    <div>
        <h2 class="map-filters-title">Filters</h2>
        <p id="activeFiltersMeta" class="map-filters-meta">0 filters applied</p>
        <div id="activeChips" class="map-filters-chips"></div>
        <button
            id="clearAllBtn"
            type="button"
            class="map-filters-clear"
        >
            Clear all
        </button>
    </div>

    <div class="map-filters-section">
        <h3 class="map-filters-subtitle">Category</h3>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="category"
                value="business"
                class="map-filters-checkbox"
            />
            Business
        </label>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="category"
                value="food"
                class="map-filters-checkbox"
            />
            Maistas ir gėrimai
        </label>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="category"
                value="health"
                class="map-filters-checkbox"
            />
            Sveikata
        </label>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="category"
                value="music"
                class="map-filters-checkbox"
            />
            Music
        </label>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="category"
                value="art"
                class="map-filters-checkbox"
            />
            Art
        </label>
        <button class="map-filters-more">
            Show more
        </button>
    </div>

    <div class="map-filters-section">
        <h3 class="map-filters-subtitle">Date</h3>
        <label class="map-filters-label">
            <input
                type="radio"
                name="dateRange"
                value="today"
                class="map-filters-radio"
            />
            Today
        </label>
        <label class="map-filters-label">
            <input
                type="radio"
                name="dateRange"
                value="tomorrow"
                class="map-filters-radio"
            />
            Tomorrow
        </label>
        <label class="map-filters-label">
            <input
                type="radio"
                name="dateRange"
                value="weekend"
                class="map-filters-radio"
            />
            This weekend
        </label>
        <label class="map-filters-label">
            <input
                type="radio"
                name="dateRange"
                value="custom"
                class="map-filters-radio"
            />
            Choose date...
        </label>
    </div>

    <div class="map-filters-section">
        <h3 class="map-filters-subtitle">District</h3>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="district"
                value="Senamiestis"
                class="map-filters-checkbox"
            />
            Senamiestis
        </label>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="district"
                value="Šnipiškės"
                class="map-filters-checkbox"
            />
            Šnipiškės
        </label>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="district"
                value="Naujamiestis"
                class="map-filters-checkbox"
            />
            Naujamiestis
        </label>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="district"
                value="Užupis"
                class="map-filters-checkbox"
            />
            Užupis
        </label>
    </div>

    <div class="map-filters-section">
        <h3 class="map-filters-subtitle">Price</h3>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="price"
                value="free"
                class="map-filters-checkbox"
            />
            Free
        </label>
        <label class="map-filters-label">
            <input
                type="checkbox"
                name="price"
                value="paid"
                class="map-filters-checkbox"
            />
            Paid
        </label>
    </div>
</aside>
