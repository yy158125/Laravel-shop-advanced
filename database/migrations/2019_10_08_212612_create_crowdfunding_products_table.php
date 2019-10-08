<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\CrowdfundingProduct;

class CreateCrowdfundingProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crowdfunding_products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->decimal('target_amount',10,2);
            $table->decimal('total_amount',10,2)->default(0);
            $table->unsignedInteger('user_count')->default(0);
            $table->dateTime('end_at');
            $table->string('status')->default(CrowdfundingProduct::STATUS_FUNDING);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crowdfunding_products');
    }
}
