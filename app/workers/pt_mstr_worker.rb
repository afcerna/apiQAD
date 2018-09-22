require 'sidekiq'
require 'sidekiq-scheduler'
require 'rubygems'
require 'csv'

class PtMstrWorker
  include Sidekiq::Worker

  def perform
    params = Hash.new

    CSV.foreach("/home/alexis/Documents/Universidad/Proyecto/apiQAD/pt_mstr.csv", { encoding: "UTF-8", headers: false, header_converters: :symbol, converters: :all}) do |row|
      fields = row[0].split(';')
      if Pt.where(:item_code => fields[0]).empty?
          params[:item_code] = fields[0]
          params[:name] = fields[2]
          params[:weight] = fields[5]
          params[:um_w] = fields[6]
          params[:price] = fields[7]
          params[:cost] = fields[8]
          params[:prod_type] = fields[9]
          params[:material] = fields[10]
          params[:route] = fields[11]
          params[:client_sku] = fields[12]
          params[:currency] = fields[13]
          Pt.create(params)
      end
    end
  end
end
