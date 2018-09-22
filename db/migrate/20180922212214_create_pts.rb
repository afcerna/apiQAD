class CreatePts < ActiveRecord::Migration[5.2]
  def change
    create_table :pts do |t|
      t.string :item_code
      t.string :name
      t.string :weight
      t.string :um_w
      t.string :price
      t.string :cost
      t.string :prod_type
      t.string :material
      t.string :route
      t.string :client_sku
      t.string :currency

      t.timestamps
    end
  end
end
