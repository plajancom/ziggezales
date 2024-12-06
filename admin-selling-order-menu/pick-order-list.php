<div class="wrap zg-pick-order">
<form id="export-form" method="POST">
    <h2>Plocklista detalj</h2>

    <div class="date-range-filter" id="landing-page-filter">
        <label for="start-date">Start datum:</label>
        <input type="text" placeholder="<?php echo DATE_FORMAT_PLACEHOLDER; ?>" id="start-date" name="start_date" />
        <label for="end-date">Slutdatum:</label>
        <input type="text" placeholder="<?php echo DATE_FORMAT_PLACEHOLDER; ?>" id="end-date" name="end_date" />
    </div>
    <div class="date-range-filter">
        <label for="pick-school-dropdown">Skola/Förening:</label>
        <select class="pick-school-dropdown" name="school_id">
            <option value="">Välj Skola/Förening</option>
            <?php 
            if( !empty($school_ids) ) {
                foreach ($school_ids as $key => $school) {  ?>
                    <option value="<?php echo $school;?>"><?php echo $school_name[$key]; ?></option>
            <?php
                }
            }
            ?>
        </select>
        <label for="pick-class-dropdown">Alternativ:</label>
        <select class="pick-class-dropdown" name="class_id">
            <option value="">Välj alternativ</option>
        </select>
        <label for="pick-seller-dropdown">Säljare:</label>
        <select class="pick-seller-dropdown" name="seller_id">
            <option value="">Välj Säljare</option>
        </select>
        
        <input type="button" class="button button-primary" value="Filtrera" id="filter-pick-order" style="margin-right: 2px;" />
        <input type="submit" name="export-pick-list" class="button button-warning" value="Export" id="export-pick-order">
    </div>

    <div class="pick-order-list">
        <div class="table-responsive">
            <table class="table table-striped" id="table-pick-order" style="width: 100%;">
                <thead>
                    <th valign="middle">Föremålsnamn</th>
                    <th valign="middle">Kategori</th>
                    <th valign="middle">Antal artiklar</th>
                    <th valign="middle">Skolans/föreningens namn</th>
                    <th valign="middle">Alternativ</th>
                    <th valign="middle">Säljare</th>
                    <th valign="middle">Beställning ID</th>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</form>
</div>