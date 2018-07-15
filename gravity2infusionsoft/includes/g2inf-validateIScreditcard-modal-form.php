<div id="ISCreditCard" data-backdrop="static" data-keyboard="false" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Infusionsoft - Checkout</h4>
      </div>
      <div class="modal-body">
        <div class='alert alert-danger' id="ccard-error"></div>
        <div class='alert alert-info note'>
          Create credit card to complete transaction.
        </div>
        <form action="/action_page.php">
          <div class="form-group">
            <select class="form-control" name="cardType" id="cardType">
              <option value="visa">Visa</option>
              <option value="american-express">American Express</option>
            </select>
          </div>
          <input id="is-cid" name="is-cid" hidden>
          <div class="form-group">
            <input type="text" class="form-control" name="cnum" id="cnum" placeholder="Card Number" required>
          </div>
          <div class="form-group">
            <div style="width: 150px; display: inline-block;">
              <input type="text" class="form-control" name="cexpmonth" min="2" id="cexpmonth" placeholder="Exp. Month(mm)" required>
            </div>
            <div style="width: 150px; display: inline-block;">
              <input type="text" class="form-control" name="cexpyear" min="4" id="cexpyear" placeholder="Exp. Year(yyyy)" required>
            </div>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="ccode" id="ccode" placeholder="Security Code" required>
          </div>
          <div class="form-group submit-btn-area">
            <button type="button" class="btn btn-default cancel" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success proceed">Submit</button>
            <div class="loader"></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>