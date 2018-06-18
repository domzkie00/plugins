<div id="ISCreateCustomer" data-backdrop="static" data-keyboard="false" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Infusionsoft - Create Customer Record</h4>
      </div>
      <div class="modal-body">
        <div class='alert alert-info note'>
          Mapped email <b class='mapped-email'></b> does not exists in Infusionsoft. 
          Please create a Infusionsoft customer record, to be able to proceed.
        </div>
        <form action="/action_page.php">
          <div class="form-group">
            <label for="fname">First Name:</label>
            <input type="text" class="form-control" name="fname" id="fname" required>
          </div>
          <div class="form-group">
            <label for="lname">Last Name:</label>
            <input type="text" class="form-control" name="lname" id="lname" required>
          </div>
          <div class="form-group">
            <label for="email">Email address:</label>
            <input type="email" class="form-control" name="email" id="email" readonly>
          </div>
          <div class="form-group submit-btn-area">
            <button type="submit" class="btn btn-success">Submit</button>
            <div class="loader"></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>