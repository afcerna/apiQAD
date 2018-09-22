class PtsController < ApplicationController

  def index
    @pts = Pt.all
    output = []
    @pts.each do |row|
      hash = {}
      hash[:item_code] = row[:item_code]
      hash[:name] = row[:name]#row[:numqad]
      hash[:weight] = row[:weight]
      hash[:um_w] = row[:um_w]
      hash[:price] = row[:price]
      hash[:cost] = row[:cost]
      hash[:prod_type] = row[:prod_type]
      hash[:material] = row[:material]
      hash[:route] = row[:route]
      hash[:client_sku] = row[:client_sku]
      hash[:currency] = row[:currency]
      output << hash
    end

    render :json => output.to_json

  end

end
