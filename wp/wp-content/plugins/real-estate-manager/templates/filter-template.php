<form id="real-estate-filter-form">
    <select name="district">
        <option value="">All Districts</option>
        <?php
        $districts = get_terms(['taxonomy' => 'district', 'hide_empty' => false]);
        foreach ($districts as $d) {
            echo '<option value="' . esc_attr($d->slug) . '">' . esc_html($d->name) . '</option>';
        }
        ?>
    </select>
    
    <label for="floors">Number of Floors:</label>
    <input type="number" name="floors" id="floors" min="1">

    <label for="building_type">Building Type:</label>
    <select name="building_type" id="building_type">
        <option value="">Any</option>
        <option value="Панель">Panel</option>
        <option value="Цегла">Brick</option>
        <option value="Піноблок">Foam Block</option>
    </select>

    <label for="eco_rating">Eco Rating:</label>
    <select name="eco_rating" id="eco_rating">
        <option value="">Any</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
    </select>

    <input type="number" name="rooms" placeholder="Number of Rooms">
    <input type="number" name="area_min" placeholder="Min Area">
    <input type="number" name="area_max" placeholder="Max Area">
    <button type="submit">Search</button>
</form>

<div id="real-estate-results"></div>
