package harus.bisa;

import java.util.ArrayList;
import java.util.HashMap;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.ListActivity;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.widget.AdapterView;
import android.widget.EditText;
import android.widget.ListAdapter;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.TextView;
import android.widget.AdapterView.OnItemClickListener;

public class ListAngkatan extends ListActivity{
	
	private EditText search_angkatan;
	private ListAdapter adapter;
	
	public void onCreate(Bundle onlist){
		super.onCreate(onlist);
		setContentView(R.layout.form_online_list);
		showAngkatan sa = new showAngkatan();
		
		sa.execute("http://192.168.1.73:8080/chat/list_angkatan.php");
		
		search_angkatan = (EditText) findViewById(R.id.txt_search_on);
		
		search_angkatan.addTextChangedListener(new TextWatcher() {

			public void afterTextChanged(Editable s) {
				// TODO Auto-generated method stub
				
			}

			public void beforeTextChanged(CharSequence s, int start, int count,
					int after) {
				// TODO Auto-generated method stub
				
			}

			public void onTextChanged(CharSequence s, int start, int before,
					int count) {
				// TODO Auto-generated method stub
				((SimpleAdapter) ListAngkatan.this.adapter).getFilter().filter(s);
				
			}
           
        });
    }
	
	class showAngkatan extends AsyncTask<String, Integer, ArrayList<HashMap<String, String>> >{
		
		ArrayList<HashMap<String, String>> mylist = new ArrayList<HashMap<String, String>>();

		@Override
		protected ArrayList<HashMap<String, String>> doInBackground(String... params) {
			// TODO Auto-generated method stub
			try {
				JSONObject json = JSONParser.getJSONfromURL(params[0]); 
				JSONArray list_angkatan = json.getJSONArray("list_angkatan");	

				for (int i = 0; i < list_angkatan.length(); i++){
					JSONObject jsonobj = list_angkatan.getJSONObject(i);
					
					HashMap<String, String> map = new HashMap<String, String>();
					if(jsonobj.getString("angkatan").toString().equals("Lecturer")){
						map.put("angkatan", jsonobj.getString("angkatan"));
					}else{
						map.put("angkatan", "Angkatan " + jsonobj.getString("angkatan"));
					}
		        	mylist.add(map);
				}
			} catch (JSONException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			return null;
		}
		
		protected void onPostExecute(ArrayList<HashMap<String, String>> result) {
			
			adapter = new SimpleAdapter(ListAngkatan.this, mylist , 
					R.layout.isi_list_awal,
					new String[] { "angkatan"},
					new int[] { R.id.txt_isi_list_awal});
			setListAdapter(adapter);
	        
	        final ListView lv = getListView();
	        lv.setOnItemClickListener(new OnItemClickListener() {
	        	public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
	        		String year = ((TextView) view.findViewById(R.id.txt_isi_list_awal)).getText().toString();
	        		String year_baru;
	        		if(year.length() >8 ){
	        			year_baru = year.substring(9, year.length());
	        			System.out.println(year_baru);
	        		}else{
	        			year_baru = year;
	        		}
					Intent in = new Intent(ListAngkatan.this, ListUser.class);
					in.putExtra("year", year_baru);
					startActivity(in);
				}
			});
		
		}

	}

}
