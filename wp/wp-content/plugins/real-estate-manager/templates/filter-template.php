<form id="real-estate-filter-form">
    <select name="district">
        <option value="">Всі райони</option>
        <?php
        $districts = get_terms(['taxonomy' => 'district', 'hide_empty' => false]);
        foreach ($districts as $d) {
            echo '<option value="' . esc_attr($d->slug) . '">' . esc_html($d->name) . '</option>';
        }
        ?>
    </select>
    
    <label for="floors">Кількість поверхів:</label>
    <input type="number" name="floors" id="floors" min="1">

    <label for="building_type">Тип будівлі:</label>
    <select name="building_type" id="building_type">
        <option value="">Будь-який</option>
        <option value="панель">Панель</option>
        <option value="цегла">Цегла</option>
        <option value="піноблок">Піноблок</option>
    </select>

    <label for="eco_rating">Екологічність:</label>
    <select name="eco_rating" id="eco_rating">
        <option value="">Будь-яка</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
    </select>

    <input type="number" name="rooms" placeholder="Кількість кімнат">
    <input type="number" name="area_min" placeholder="Мін. площа">
    <input type="number" name="area_max" placeholder="Макс. площа">
    <button type="submit">Знайти</button>
</form>

<div id="real-estate-results"></div>
